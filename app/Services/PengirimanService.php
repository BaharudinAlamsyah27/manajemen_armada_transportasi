<?php

namespace App\Services;

use App\Enums\StatusKendaraan;
use App\Enums\StatusPengiriman;
use App\Enums\StatusPesanan;
use App\Models\BuktiPengiriman;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                // Kesesuaian SIM (A8): motor -> C; pickup/box -> B1/B2; truk -> B2.
                $simCocok = match ($kendaraan->jenis) {
                    'motor' => $sopir->jenis_sim === 'C',
                    'pickup', 'box' => in_array($sopir->jenis_sim, ['B1', 'B2'], true),
                    'truk' => $sopir->jenis_sim === 'B2',
                    default => true,
                };
                if (!$simCocok) {
                    $errors[] = "Jenis SIM sopir ({$sopir->jenis_sim}) tidak sesuai untuk kendaraan {$kendaraan->jenis}.";
                }
                if (!empty($errors)) {
                    throw new Exception('Dokumen tidak laik jalan: ' . implode(' ', $errors));
                }
            }

            // Cek tumpang tindih jendela waktu multi-rit + jeda antar rit (A5)
            $waktuMulai = isset($data['waktu_mulai_rencana']) ? Carbon::parse($data['waktu_mulai_rencana']) : now();
            $estimasiTiba = isset($data['estimasi_tiba']) ? Carbon::parse($data['estimasi_tiba']) : $waktuMulai->copy()->addHours(4);
            $jedaMenit = (int) Setting::getValue('jeda_antar_rit_menit', 30);

            $bentrokKendaraan = Pengiriman::where('kendaraan_id', $kendaraan->id)
                ->whereIn('status', [StatusPengiriman::TERJADWAL->value, StatusPengiriman::BERJALAN->value])
                ->whereNotNull('waktu_mulai_rencana')
                ->whereNotNull('estimasi_tiba')
                ->get()
                ->first(function (Pengiriman $p) use ($waktuMulai, $estimasiTiba, $jedaMenit) {
                    $mulaiLain = $p->waktu_mulai_rencana->copy()->subMinutes($jedaMenit);
                    $selesaiLain = $p->estimasi_tiba->copy()->addMinutes($jedaMenit);
                    return $waktuMulai->lt($selesaiLain) && $estimasiTiba->gt($mulaiLain);
                });

            if ($bentrokKendaraan) {
                throw new Exception("Jadwal kendaraan bentrok dengan pengiriman {$bentrokKendaraan->kode} (termasuk jeda {$jedaMenit} menit).");
            }

            $bentrokSopir = Pengiriman::where('sopir_id', $sopir->id)
                ->whereIn('status', [StatusPengiriman::TERJADWAL->value, StatusPengiriman::BERJALAN->value])
                ->whereNotNull('waktu_mulai_rencana')
                ->whereNotNull('estimasi_tiba')
                ->get()
                ->first(function (Pengiriman $p) use ($waktuMulai, $estimasiTiba, $jedaMenit) {
                    $mulaiLain = $p->waktu_mulai_rencana->copy()->subMinutes($jedaMenit);
                    $selesaiLain = $p->estimasi_tiba->copy()->addMinutes($jedaMenit);
                    return $waktuMulai->lt($selesaiLain) && $estimasiTiba->gt($mulaiLain);
                });

            if ($bentrokSopir) {
                throw new Exception("Jadwal sopir bentrok dengan pengiriman {$bentrokSopir->kode} (termasuk jeda {$jedaMenit} menit).");
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
                'waktu_mulai_rencana' => $waktuMulai,
                'estimasi_tiba' => $estimasiTiba,
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

    public function mulaiPerjalanan(Pengiriman $pengiriman, int $odometerAwal, string $sumber = 'admin'): Pengiriman
    {
        return DB::transaction(function () use ($pengiriman, $odometerAwal, $sumber) {
            $pengiriman = Pengiriman::withoutGlobalScopes()->lockForUpdate()->findOrFail($pengiriman->id);
            if ($pengiriman->status !== StatusPengiriman::TERJADWAL->value) {
                throw new Exception('Hanya pengiriman berstatus terjadwal yang dapat dimulai.');
            }

            $kendaraan = Kendaraan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pengiriman->kendaraan_id);
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
                        "Pengiriman {$pengiriman->kode} mulai berjalan.",
                        $sumber
                    );
                }
            }

            return $pengiriman;
        });
    }

    /**
     * Tandai satu pesanan terkirim. Sopir wajib foto (A19); admin boleh tanpa foto dengan catatan.
     */
    public function tandaiTerkirim(
        Pesanan $pesanan,
        string $namaPenerima,
        ?UploadedFile $foto = null,
        ?string $catatan = null,
        string $sumber = 'admin'
    ): Pesanan {
        if (trim($namaPenerima) === '') {
            throw new Exception('Nama penerima aktual wajib diisi.');
        }
        if ($sumber === 'sopir' && !$foto) {
            throw new Exception('Foto bukti pengiriman wajib diunggah.');
        }
        if ($sumber !== 'sopir' && !$foto && empty($catatan)) {
            throw new Exception('Input manual tanpa foto wajib disertai catatan.');
        }

        // Simpan berkas di luar transaksi; bila transaksi gagal, berkas dihapus lagi.
        $path = $foto ? $foto->store('bukti-pengiriman', 'local') : null;

        try {
            return DB::transaction(function () use ($pesanan, $namaPenerima, $foto, $path, $catatan, $sumber) {
                $pesanan = Pesanan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pesanan->id);
                $pengiriman = $pesanan->pengiriman;

                if (!$pengiriman || $pengiriman->status !== StatusPengiriman::BERJALAN->value) {
                    throw new Exception('Pesanan hanya dapat ditandai terkirim saat pengirimannya sedang berjalan.');
                }
                if ($pesanan->status !== StatusPesanan::DALAM_PERJALANAN->value) {
                    throw new Exception('Pesanan ini tidak sedang dalam perjalanan (mungkin sudah diproses).');
                }

                BuktiPengiriman::create([
                    'pesanan_id' => $pesanan->id,
                    'pengiriman_id' => $pengiriman->id,
                    'jenis' => $foto ? 'foto' : 'manual',
                    'path' => $path,
                    'nama_penerima' => $namaPenerima,
                    'catatan' => $catatan,
                    'diambil_oleh' => Auth::id(),
                    'waktu' => now(),
                    'ukuran' => $foto?->getSize(),
                    'mime' => $foto?->getMimeType(),
                ]);

                (new PesananService())->updateStatus(
                    $pesanan,
                    StatusPesanan::TERKIRIM->value,
                    'Diterima oleh ' . $namaPenerima . ($catatan ? " — {$catatan}" : ''),
                    $sumber
                );

                return $pesanan;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function tandaiGagal(Pesanan $pesanan, string $alasan, string $sumber = 'admin'): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $alasan, $sumber) {
            $pesanan = Pesanan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pesanan->id);
            $pengiriman = $pesanan->pengiriman;

            if (!$pengiriman || $pengiriman->status !== StatusPengiriman::BERJALAN->value) {
                throw new Exception('Pesanan hanya dapat ditandai gagal saat pengirimannya sedang berjalan.');
            }

            (new PesananService())->updateStatus($pesanan, StatusPesanan::GAGAL_KIRIM->value, $alasan, $sumber);

            return $pesanan;
        });
    }

    public function selesaiPerjalanan(Pengiriman $pengiriman, int $odometerAkhir, string $sumber = 'admin'): Pengiriman
    {
        return DB::transaction(function () use ($pengiriman, $odometerAkhir) {
            $pengiriman = Pengiriman::withoutGlobalScopes()->lockForUpdate()->findOrFail($pengiriman->id);
            if ($pengiriman->status !== StatusPengiriman::BERJALAN->value) {
                throw new Exception('Hanya pengiriman yang sedang berjalan yang dapat diselesaikan.');
            }

            if ($odometerAkhir < (int) $pengiriman->odometer_awal) {
                throw new Exception("Odometer akhir ({$odometerAkhir} km) tidak boleh lebih kecil dari odometer awal ({$pengiriman->odometer_awal} km).");
            }

            // Pedoman 6.3: semua pesanan harus sudah final (terkirim / gagal_kirim).
            $belumFinal = $pengiriman->pesanans
                ->whereNotIn('status', [StatusPesanan::TERKIRIM->value, StatusPesanan::GAGAL_KIRIM->value]);
            if ($belumFinal->isNotEmpty()) {
                throw new Exception('Masih ada ' . $belumFinal->count() . ' pesanan yang belum ditandai terkirim/gagal: ' . $belumFinal->pluck('kode_resi')->implode(', ') . '.');
            }

            // Pesanan gagal dilepas agar bisa dijadwalkan ulang.
            foreach ($pengiriman->pesanans as $pesanan) {
                if ($pesanan->status === StatusPesanan::GAGAL_KIRIM->value) {
                    $pesanan->update(['pengiriman_id' => null]);
                }
            }

            $pengiriman->update([
                'status' => StatusPengiriman::SELESAI->value,
                'odometer_akhir' => $odometerAkhir,
                'waktu_tiba' => now(),
            ]);

            $kendaraan = Kendaraan::withoutGlobalScopes()->lockForUpdate()->findOrFail($pengiriman->kendaraan_id);
            $kendaraan->update([
                'status' => StatusKendaraan::TERSEDIA->value,
                'odometer_terakhir' => $odometerAkhir,
            ]);

            return $pengiriman;
        });
    }

    public function batal(Pengiriman $pengiriman, string $alasan): Pengiriman
    {
        if (trim($alasan) === '') {
            throw new Exception('Alasan pembatalan wajib diisi.');
        }

        return DB::transaction(function () use ($pengiriman, $alasan) {
            $pengiriman = Pengiriman::withoutGlobalScopes()->lockForUpdate()->findOrFail($pengiriman->id);
            if ($pengiriman->status !== StatusPengiriman::TERJADWAL->value) {
                throw new Exception('Hanya pengiriman berstatus terjadwal yang dapat dibatalkan.');
            }

            $pesananService = new PesananService();
            foreach ($pengiriman->pesanans as $pesanan) {
                $pesananService->updateStatus(
                    $pesanan,
                    StatusPesanan::MENUNGGU_JADWAL->value,
                    "Pengiriman {$pengiriman->kode} dibatalkan: {$alasan}"
                );
            }

            $pengiriman->update([
                'status' => StatusPengiriman::BATAL->value,
                'catatan' => trim(($pengiriman->catatan ? $pengiriman->catatan . "\n" : '') . "Dibatalkan: {$alasan}"),
            ]);

            return $pengiriman;
        });
    }
}
