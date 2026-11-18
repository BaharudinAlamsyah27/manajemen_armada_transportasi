<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\PeriodeAkuntansi;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JurnalService
{
    public function pastikanPeriodeTerbuka(Carbon|string $tanggal): PeriodeAkuntansi
    {
        $tgl = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);
        $periode = PeriodeAkuntansi::firstOrCreate(
            ['tahun' => (int) $tgl->year, 'bulan' => (int) $tgl->month],
            ['status' => 'buka']
        );

        if ($periode->status === 'tutup') {
            throw new Exception("Periode akuntansi {$tgl->format('m/Y')} sudah ditutup. Transaksi ditolak.");
        }

        return $periode;
    }

    /**
     * Buat jurnal (draf atau langsung posted). Wajib seimbang: total debit == total kredit.
     */
    public function buat(array $header, array $baris, bool $langsungPost = false): Jurnal
    {
        if (count($baris) < 2) {
            throw new Exception('Jurnal minimal harus memiliki 2 baris (debit dan kredit).');
        }

        $totalDebit = '0.00';
        $totalKredit = '0.00';
        foreach ($baris as $b) {
            $d = round((float) ($b['debit'] ?? 0), 2);
            $k = round((float) ($b['kredit'] ?? 0), 2);
            if ($d < 0 || $k < 0 || ($d == 0.0 && $k == 0.0)) {
                throw new Exception('Setiap baris jurnal harus bernilai positif pada debit atau kredit.');
            }
            $totalDebit = bcadd($totalDebit, number_format($d, 2, '.', ''), 2);
            $totalKredit = bcadd($totalKredit, number_format($k, 2, '.', ''), 2);
        }

        if (bccomp($totalDebit, $totalKredit, 2) !== 0) {
            throw new Exception("Jurnal tidak seimbang: Total Debit (Rp {$totalDebit}) != Total Kredit (Rp {$totalKredit}).");
        }

        return DB::transaction(function () use ($header, $baris, $langsungPost) {
            $tanggal = Carbon::parse($header['tanggal']);
            $periode = $this->pastikanPeriodeTerbuka($tanggal);
            $cabangId = $header['cabang_id'] ?? Auth::user()?->cabang_id;

            $noJurnal = NomorDokumenService::buat($cabangId, 'JU', $tanggal->format('Ym'));

            $jurnal = Jurnal::create([
                'cabang_id' => $cabangId,
                'no_jurnal' => $noJurnal,
                'tanggal' => $tanggal->toDateString(),
                'periode_id' => $periode->id,
                'keterangan' => $header['keterangan'],
                'jenis' => $header['jenis'] ?? 'manual',
                'peristiwa' => $header['peristiwa'] ?? null,
                'sumber_type' => $header['sumber_type'] ?? null,
                'sumber_id' => $header['sumber_id'] ?? null,
                'status' => $langsungPost ? 'posted' : 'draf',
                'membalik_jurnal_id' => $header['membalik_jurnal_id'] ?? null,
                'dibuat_oleh' => $header['dibuat_oleh'] ?? Auth::id(),
                'disetujui_oleh' => $langsungPost ? ($header['disetujui_oleh'] ?? Auth::id()) : null,
                'diposting_pada' => $langsungPost ? now() : null,
            ]);

            foreach ($baris as $b) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id' => $b['akun_id'],
                    'debit' => round((float) ($b['debit'] ?? 0), 2),
                    'kredit' => round((float) ($b['kredit'] ?? 0), 2),
                    'keterangan' => $b['keterangan'] ?? null,
                    'kendaraan_id' => $b['kendaraan_id'] ?? null,
                    'pengiriman_id' => $b['pengiriman_id'] ?? null,
                    'pelanggan_id' => $b['pelanggan_id'] ?? null,
                ]);
            }

            return $jurnal;
        });
    }

    public function posting(Jurnal $jurnal): Jurnal
    {
        return DB::transaction(function () use ($jurnal) {
            $jurnal = Jurnal::withoutGlobalScopes()->lockForUpdate()->with('details')->findOrFail($jurnal->id);

            if ($jurnal->status === 'posted') {
                throw new Exception('Jurnal sudah berstatus posted dan tidak dapat diubah.');
            }

            $this->pastikanPeriodeTerbuka($jurnal->tanggal);

            $userId = Auth::id();
            $wajibBeda = Setting::getValue('wajib_persetujuan_berbeda_user', '1') === '1';
            if ($wajibBeda && $jurnal->jenis === 'manual' && $jurnal->dibuat_oleh && (int) $jurnal->dibuat_oleh === (int) $userId) {
                throw new Exception('Pembuat dan penyetuju jurnal manual harus pengguna yang berbeda (maker-checker).');
            }

            $debit = $jurnal->details->sum('debit');
            $kredit = $jurnal->details->sum('kredit');
            if (bccomp((string) $debit, (string) $kredit, 2) !== 0) {
                throw new Exception('Jurnal tidak seimbang, gagal posting.');
            }

            $jurnal->update([
                'status' => 'posted',
                'disetujui_oleh' => $userId,
                'diposting_pada' => now(),
            ]);

            return $jurnal;
        });
    }

    public function balik(Jurnal $jurnalAsli, string $alasan): Jurnal
    {
        if ($jurnalAsli->status !== 'posted') {
            throw new Exception('Hanya jurnal berstatus posted yang dapat dibalik.');
        }

        $barisBalik = [];
        foreach ($jurnalAsli->details as $d) {
            $barisBalik[] = [
                'akun_id' => $d->akun_id,
                'debit' => (float) $d->kredit,
                'kredit' => (float) $d->debit,
                'keterangan' => "Jurnal balik {$jurnalAsli->no_jurnal}: {$alasan}",
                'kendaraan_id' => $d->kendaraan_id,
                'pengiriman_id' => $d->pengiriman_id,
                'pelanggan_id' => $d->pelanggan_id,
            ];
        }

        return $this->buat([
            'cabang_id' => $jurnalAsli->cabang_id,
            'tanggal' => now()->toDateString(),
            'keterangan' => "Jurnal Balik #{$jurnalAsli->no_jurnal} — {$alasan}",
            'jenis' => 'balik',
            'membalik_jurnal_id' => $jurnalAsli->id,
        ], $barisBalik, true);
    }
}
