<?php

namespace App\Services;

use App\Enums\StatusKendaraan;
use App\Enums\StatusPengiriman;
use App\Enums\StatusPesanan;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengirimanService
{
    public function createManifest(array $data, array $pesananIds): Pengiriman
    {
        return DB::transaction(function () use ($data, $pesananIds) {
            $user = Auth::user();
            $tanggalRencana = isset($data['tanggal_rencana'])
                ? Carbon::parse($data['tanggal_rencana'])->toDateString()
                : now()->toDateString();

            $kendaraan = Kendaraan::lockForUpdate()->findOrFail($data['kendaraan_id']);
            $sopir = Karyawan::lockForUpdate()->findOrFail($data['sopir_id']);

            $cabangId = $kendaraan->cabang_id;

            if ($sopir->cabang_id !== $cabangId) {
                throw new Exception('Kendaraan dan sopir harus berasal dari cabang yang sama.');
            }

            if ($sopir->jabatan !== 'sopir' || !$sopir->aktif) {
                throw new Exception('Karyawan yang dipilih bukan sopir aktif.');
            }

            if (in_array($kendaraan->status, [StatusKendaraan::SERVIS->value, StatusKendaraan::NONAKTIF->value], true)) {
                throw new Exception("Kendaraan sedang berstatus '{$kendaraan->status}' dan tidak dapat dijadwalkan.");
            }

            $canOverride = $user && $user->hasRole('admin_pusat') && !empty($data['override_alasan']);

            if (!$canOverride) {
                $errors = [];
                if ($kendaraan->stnk_berlaku_sampai && $kendaraan->stnk_berlaku_sampai->lt($tanggalRencana)) {
                    $errors[] = 'STNK kendaraan sudah kedaluwarsa.';
                }
                if ($kendaraan->kir_berlaku_sampai && $kendaraan->kir_berlaku_sampai->lt($tanggalRencana)) {
                    $errors[] = 'KIR kendaraan sudah kedaluwarsa.';
                }
                if ($kendaraan->pajak_berlaku_sampai && $kendaraan->pajak_berlaku_sampai->lt($tanggalRencana)) {
                    $errors[] = 'Pajak kendaraan sudah kedaluwarsa.';
                }
                if ($sopir->sim_berlaku_sampai && $sopir->sim_berlaku_sampai->lt($tanggalRencana)) {
                    $errors[] = 'SIM sopir sudah kedaluwarsa.';
                }
                if (!empty($errors)) {
                    throw new Exception('Dokumen tidak laik jalan: ' . implode(' ', $errors));
                }
            }

            $pesanans = Pesanan::whereIn('id', $pesananIds)
                ->where('cabang_id', $cabangId)
                ->where('status', StatusPesanan::MENUNGGU_JADWAL->value)
                ->lockForUpdate()
                ->get();

            if ($pesanans->count() !== count($pesananIds) || $pesanans->isEmpty()) {
                throw new Exception('Pesanan tidak valid, berbeda cabang, atau sudah dijadwalkan.');
            }

            $totalMuatan = (float) $pesanans->sum('berat_kg');
            if ($totalMuatan > (float) $kendaraan->kapasitas_kg) {
                throw new Exception("Overload: Total muatan ({$totalMuatan} kg) melebihi kapasitas kendaraan ({$kendaraan->kapasitas_kg} kg).");
            }

            $ritKe = Pengiriman::where('kendaraan_id', $kendaraan->id)
                ->whereDate('tanggal_rencana', $tanggalRencana)
                ->whereNotIn('status', [StatusPengiriman::BATAL->value])
                ->count() + 1;

            $kode = NomorDokumenService::buat($cabangId, 'PGR', Carbon::parse($tanggalRencana)->format('Ym'));

            $pengiriman = Pengiriman::create([
                'cabang_id' => $cabangId,
                'kode' => $kode,
                'tanggal_rencana' => $tanggalRencana,
                'kendaraan_id' => $kendaraan->id,
                'sopir_id' => $sopir->id,
                'kernet_id' => $data['kernet_id'] ?? null,
                'tujuan_ringkas' => $data['tujuan_ringkas'] ?? $pesanans->pluck('kota_tujuan')->unique()->implode(', '),
                'waktu_mulai_rencana' => $data['waktu_mulai_rencana'] ?? now(),
                'estimasi_tiba' => $data['estimasi_tiba'] ?? now()->addHours(4),
                'rit_ke' => $ritKe,
                'status' => StatusPengiriman::TERJADWAL->value,
                'catatan' => $data['catatan'] ?? null,
                'dibuat_oleh' => $user ? $user->id : null,
                'override_alasan' => $data['override_alasan'] ?? null,
            ]);

            $pesananService = new PesananService();
            foreach ($pesanans as $pesanan) {
                $pesanan->update(['pengiriman_id' => $pengiriman->id]);
                $pesananService->updateStatus(
                    $pesanan,
                    StatusPesanan::TERJADWAL->value,
                    "Dijadwalkan pada pengiriman {$kode}."
                );
            }

            return $pengiriman;
        });
    }

    public function mulaiPerjalanan(Pengiriman $pengiriman, int $odometerAwal): Pengiriman
    {
        return DB::transaction(function () use ($pengiriman, $odometerAwal) {
            if ($pengiriman->status !== StatusPengiriman::TERJADWAL->value) {
                throw new Exception('Hanya pengiriman berstatus terjadwal yang dapat dimulai.');
            }

            $kendaraan = Kendaraan::lockForUpdate()->findOrFail($pengiriman->kendaraan_id);
            if ($odometerAwal < $kendaraan->odometer_terakhir) {
                throw new Exception("Odometer awal ({$odometerAwal} km) tidak boleh lebih kecil dari odometer terakhir kendaraan ({$kendaraan->odometer_terakhir} km).");
            }

            $pengiriman->update([
                'status' => StatusPengiriman::BERJALAN->value,
                'odometer_awal' => $odometerAwal,
                'waktu_berangkat' => now(),
            ]);

            $kendaraan->update(['status' => StatusKendaraan::BERTUGAS->value]);

            $pesananService = new PesananService();
            foreach ($pengiriman->pesanans as $pesanan) {
                if ($pesanan->status === StatusPesanan::TERJADWAL->value) {
                    $pesananService->updateStatus(
                        $pesanan,
                        StatusPesanan::DALAM_PERJALANAN->value,
                        "Pengiriman {$pengiriman->kode} mulai berjalan."
                    );
                }
            }

            return $pengiriman;
        });
    }

    public function selesaiPerjalanan(Pengiriman $pengiriman, int $odometerAkhir): Pengiriman
    {
        return DB::transaction(function () use ($pengiriman, $odometerAkhir) {
            if ($pengiriman->status !== StatusPengiriman::BERJALAN->value) {
                throw new Exception('Hanya pengiriman yang sedang berjalan yang dapat diselesaikan.');
            }

            if ($odometerAkhir < (int) $pengiriman->odometer_awal) {
                throw new Exception("Odometer akhir ({$odometerAkhir} km) tidak boleh lebih kecil dari odometer awal ({$pengiriman->odometer_awal} km).");
            }

            $pesananService = new PesananService();
            foreach ($pengiriman->pesanans as $pesanan) {
                if ($pesanan->status === StatusPesanan::DALAM_PERJALANAN->value) {
                    $pesananService->updateStatus(
                        $pesanan,
                        StatusPesanan::TERKIRIM->value,
                        "Pengiriman {$pengiriman->kode} selesai."
                    );
                } elseif ($pesanan->status === StatusPesanan::GAGAL_KIRIM->value) {
                    $pesanan->update(['pengiriman_id' => null]);
                }
            }

            $pengiriman->update([
                'status' => StatusPengiriman::SELESAI->value,
                'odometer_akhir' => $odometerAkhir,
                'waktu_tiba' => now(),
            ]);

            $kendaraan = Kendaraan::lockForUpdate()->findOrFail($pengiriman->kendaraan_id);
            $kendaraan->update([
                'status' => StatusKendaraan::TERSEDIA->value,
                'odometer_terakhir' => $odometerAkhir,
            ]);

            return $pengiriman;
        });
    }
}
