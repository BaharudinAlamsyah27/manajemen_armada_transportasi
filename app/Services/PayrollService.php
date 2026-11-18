<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Kasbon;
use App\Models\KomponenGaji;
use App\Models\Pengiriman;
use App\Models\PeriodeGaji;
use App\Models\Setting;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function hitungPeriode(int $cabangId, int $tahun, int $bulan): PeriodeGaji
    {
        return DB::transaction(function () use ($cabangId, $tahun, $bulan) {
            $periode = PeriodeGaji::withoutGlobalScopes()->firstOrCreate(
                ['cabang_id' => $cabangId, 'tahun' => $tahun, 'bulan' => $bulan],
                ['status' => 'draf', 'dibuat_oleh' => Auth::id()]
            );

            if (!in_array($periode->status, ['draf', 'dihitung'], true)) {
                throw new Exception('Periode gaji yang sudah disetujui/dibayar tidak dapat dihitung ulang.');
            }

            $periode->slips()->delete();

            $karyawans = Karyawan::withoutGlobalScopes()
                ->where('cabang_id', $cabangId)
                ->where('aktif', true)
                ->get();

            $komponens = KomponenGaji::where('aktif', true)->get();
            $prefixBulan = sprintf('%04d-%02d', $tahun, $bulan);

            foreach ($karyawans as $k) {
                $hariHadir = Absensi::withoutGlobalScopes()
                    ->where('karyawan_id', $k->id)
                    ->where('tanggal', 'like', "{$prefixBulan}%")
                    ->where('status', 'hadir')
                    ->count();

                $jumlahRitase = Pengiriman::withoutGlobalScopes()
                    ->where('sopir_id', $k->id)
                    ->where('status', 'selesai')
                    ->where('tanggal_rencana', 'like', "{$prefixBulan}%")
                    ->count();

                $totalPendapatan = 0.0;
                $totalPotongan = 0.0;
                $items = [];

                foreach ($komponens as $komp) {
                    $nominal = (float) $komp->nominal_default;
                    $nilai = match ($komp->tipe_hitung) {
                        'tetap' => $nominal,
                        'per_hari_hadir' => $nominal * $hariHadir,
                        'per_ritase' => $k->jabatan === 'sopir' ? ($nominal * $jumlahRitase) : 0.0,
                        default => 0.0,
                    };

                    if ($nilai <= 0) {
                        continue;
                    }

                    if ($komp->jenis === 'pendapatan') {
                        $totalPendapatan += $nilai;
                    } else {
                        $totalPotongan += $nilai;
                    }

                    $items[] = [
                        'komponen_id' => $komp->id,
                        'keterangan' => $komp->nama,
                        'jenis' => $komp->jenis,
                        'jumlah' => $nilai,
                    ];
                }

                // Potongan cicilan kasbon aktif
                $kasbonAktif = Kasbon::withoutGlobalScopes()
                    ->where('karyawan_id', $k->id)
                    ->where('status', 'aktif')
                    ->where('sisa', '>', 0)
                    ->get();

                foreach ($kasbonAktif as $kb) {
                    $potong = min((float) $kb->cicilan_per_bulan, (float) $kb->sisa);
                    if ($potong > 0) {
                        $totalPotongan += $potong;
                        $items[] = [
                            'komponen_id' => null,
                            'keterangan' => "Cicilan Kasbon #{$kb->id}",
                            'jenis' => 'potongan',
                            'jumlah' => $potong,
                        ];
                    }
                }

                $gajiBersih = round($totalPendapatan - $totalPotongan, 2);
                if ($gajiBersih < 0) {
                    throw new Exception("Gaji bersih {$k->nama} bernilai negatif (Rp {$gajiBersih}). Periksa potongan kasbon.");
                }

                $slip = SlipGaji::create([
                    'periode_gaji_id' => $periode->id,
                    'karyawan_id' => $k->id,
                    'no_slip' => NomorDokumenService::buat($cabangId, 'SLP', sprintf('%04d%02d', $tahun, $bulan)),
                    'total_pendapatan' => $totalPendapatan,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'hari_hadir' => $hariHadir,
                    'jumlah_ritase' => $jumlahRitase,
                ]);

                foreach ($items as $it) {
                    SlipGajiItem::create(array_merge(['slip_id' => $slip->id], $it));
                }
            }

            $periode->update(['status' => 'dihitung']);

            return $periode->fresh('slips');
        });
    }

    public function setujui(PeriodeGaji $periode): PeriodeGaji
    {
        return DB::transaction(function () use ($periode) {
            if ($periode->status !== 'dihitung') {
                throw new Exception('Periode gaji harus berstatus dihitung sebelum disetujui.');
            }

            $userId = Auth::id();
            $wajibBeda = Setting::getValue('wajib_persetujuan_berbeda_user', '1') === '1';
            if ($wajibBeda && $periode->dibuat_oleh && (int) $periode->dibuat_oleh === (int) $userId) {
                throw new Exception('Pembuat dan penyetuju penggajian harus pengguna yang berbeda (maker-checker).');
            }

            $totalPendapatan = (float) $periode->slips()->sum('total_pendapatan');
            $totalGajiBersih = (float) $periode->slips()->sum('gaji_bersih');
            $totalPotongan = (float) $periode->slips()->sum('total_potongan');

            (new JurnalOtomatisService())->catatGajiDisetujui($periode, $totalPendapatan, $totalPotongan, $totalGajiBersih);

            $periode->update(['status' => 'disetujui', 'disetujui_oleh' => $userId]);

            return $periode;
        });
    }

    public function bayarDanPublikasikan(PeriodeGaji $periode, int $akunKasId, string $tanggalBayar): PeriodeGaji
    {
        return DB::transaction(function () use ($periode, $akunKasId, $tanggalBayar) {
            if ($periode->status !== 'disetujui') {
                throw new Exception('Periode gaji harus disetujui sebelum dibayar.');
            }

            $totalBersih = (float) $periode->slips()->sum('gaji_bersih');
            $periode->update([
                'status' => 'dipublikasikan',
                'tanggal_bayar' => $tanggalBayar,
                'akun_kas_id' => $akunKasId,
            ]);

            (new JurnalOtomatisService())->catatGajiDibayar($periode, $totalBersih);

            return $periode;
        });
    }
}
