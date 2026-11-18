<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Invoice;
use App\Models\Jurnal;
use App\Models\Pembayaran;
use App\Models\PeriodeGaji;
use App\Models\ServisKendaraan;
use Exception;

class JurnalOtomatisService
{
    private function akun(string $kode): int
    {
        $akun = Akun::where('kode', $kode)->first();
        if (!$akun) {
            throw new Exception("Akun sistem dengan kode {$kode} belum tersedia.");
        }
        return $akun->id;
    }

    private function sudahAda(string $sumberType, int $sumberId, string $peristiwa): ?Jurnal
    {
        return Jurnal::withoutGlobalScopes()
            ->where('sumber_type', $sumberType)
            ->where('sumber_id', $sumberId)
            ->where('peristiwa', $peristiwa)
            ->first();
    }

    public function catatInvoiceTerbit(Invoice $invoice): Jurnal
    {
        if ($ada = $this->sudahAda(Invoice::class, $invoice->id, 'invoice_terbit')) {
            return $ada;
        }

        $baris = [
            ['akun_id' => $this->akun('1-1300'), 'debit' => (float) $invoice->total, 'kredit' => 0, 'pelanggan_id' => $invoice->pelanggan_id],
            ['akun_id' => $this->akun('4-1100'), 'debit' => 0, 'kredit' => (float) $invoice->subtotal, 'pelanggan_id' => $invoice->pelanggan_id],
        ];
        if ((float) $invoice->ppn_nominal > 0) {
            $baris[] = ['akun_id' => $this->akun('2-1300'), 'debit' => 0, 'kredit' => (float) $invoice->ppn_nominal, 'pelanggan_id' => $invoice->pelanggan_id];
        }

        return (new JurnalService())->buat([
            'cabang_id' => $invoice->cabang_id,
            'tanggal' => $invoice->tanggal,
            'keterangan' => "Invoice Terbit {$invoice->no_invoice}",
            'jenis' => 'otomatis',
            'peristiwa' => 'invoice_terbit',
            'sumber_type' => Invoice::class,
            'sumber_id' => $invoice->id,
        ], $baris, true);
    }

    public function catatPembayaranPelanggan(Pembayaran $pembayaran): Jurnal
    {
        if ($ada = $this->sudahAda(Pembayaran::class, $pembayaran->id, 'pembayaran_pelanggan')) {
            return $ada;
        }

        $totalPiutang = (float) $pembayaran->jumlah_diterima + (float) $pembayaran->potongan_pajak;
        $baris = [
            ['akun_id' => $pembayaran->akun_kas_id, 'debit' => (float) $pembayaran->jumlah_diterima, 'kredit' => 0, 'pelanggan_id' => $pembayaran->pelanggan_id],
            ['akun_id' => $this->akun('1-1300'), 'debit' => 0, 'kredit' => $totalPiutang, 'pelanggan_id' => $pembayaran->pelanggan_id],
        ];
        if ((float) $pembayaran->potongan_pajak > 0) {
            $baris[] = ['akun_id' => $this->akun('1-1650'), 'debit' => (float) $pembayaran->potongan_pajak, 'kredit' => 0, 'pelanggan_id' => $pembayaran->pelanggan_id];
        }

        return (new JurnalService())->buat([
            'cabang_id' => $pembayaran->cabang_id,
            'tanggal' => $pembayaran->tanggal,
            'keterangan' => "Pembayaran Pelanggan {$pembayaran->no_pembayaran}",
            'jenis' => 'otomatis',
            'peristiwa' => 'pembayaran_pelanggan',
            'sumber_type' => Pembayaran::class,
            'sumber_id' => $pembayaran->id,
        ], $baris, true);
    }

    public function catatBbmTerverifikasi(BbmLog $bbm): Jurnal
    {
        if ($ada = $this->sudahAda(BbmLog::class, $bbm->id, 'bbm_terverifikasi')) {
            return $ada;
        }

        return (new JurnalService())->buat([
            'cabang_id' => $bbm->cabang_id,
            'tanggal' => $bbm->tanggal,
            'keterangan' => "BBM Terverifikasi #{$bbm->id}",
            'jenis' => 'otomatis',
            'peristiwa' => 'bbm_terverifikasi',
            'sumber_type' => BbmLog::class,
            'sumber_id' => $bbm->id,
        ], [
            ['akun_id' => $this->akun('5-1100'), 'debit' => (float) $bbm->total, 'kredit' => 0, 'kendaraan_id' => $bbm->kendaraan_id, 'pengiriman_id' => $bbm->pengiriman_id],
            ['akun_id' => $this->akun('1-1100'), 'debit' => 0, 'kredit' => (float) $bbm->total, 'kendaraan_id' => $bbm->kendaraan_id, 'pengiriman_id' => $bbm->pengiriman_id],
        ], true);
    }

    public function catatBiayaOperasional(BiayaOperasional $biaya): Jurnal
    {
        if ($ada = $this->sudahAda(BiayaOperasional::class, $biaya->id, 'biaya_terverifikasi')) {
            return $ada;
        }

        $akunBeban = in_array($biaya->jenis, ['tol', 'parkir'], true) ? '5-1300' : '5-1400';

        return (new JurnalService())->buat([
            'cabang_id' => $biaya->cabang_id,
            'tanggal' => $biaya->tanggal,
            'keterangan' => "Biaya Operasional ({$biaya->jenis}) #{$biaya->id}",
            'jenis' => 'otomatis',
            'peristiwa' => 'biaya_terverifikasi',
            'sumber_type' => BiayaOperasional::class,
            'sumber_id' => $biaya->id,
        ], [
            ['akun_id' => $this->akun($akunBeban), 'debit' => (float) $biaya->jumlah, 'kredit' => 0, 'kendaraan_id' => $biaya->kendaraan_id, 'pengiriman_id' => $biaya->pengiriman_id],
            ['akun_id' => $this->akun('1-1100'), 'debit' => 0, 'kredit' => (float) $biaya->jumlah, 'kendaraan_id' => $biaya->kendaraan_id, 'pengiriman_id' => $biaya->pengiriman_id],
        ], true);
    }

    public function catatServisSelesai(ServisKendaraan $servis): Jurnal
    {
        if ($ada = $this->sudahAda(ServisKendaraan::class, $servis->id, 'servis_selesai')) {
            return $ada;
        }

        return (new JurnalService())->buat([
            'cabang_id' => $servis->cabang_id,
            'tanggal' => $servis->tanggal_selesai ?? now()->toDateString(),
            'keterangan' => "Servis Kendaraan #{$servis->id}",
            'jenis' => 'otomatis',
            'peristiwa' => 'servis_selesai',
            'sumber_type' => ServisKendaraan::class,
            'sumber_id' => $servis->id,
        ], [
            ['akun_id' => $this->akun('5-1200'), 'debit' => (float) $servis->total_biaya, 'kredit' => 0, 'kendaraan_id' => $servis->kendaraan_id],
            ['akun_id' => $this->akun('1-1100'), 'debit' => 0, 'kredit' => (float) $servis->total_biaya, 'kendaraan_id' => $servis->kendaraan_id],
        ], true);
    }

    public function catatGajiDisetujui(PeriodeGaji $periode, float $totalPendapatan, float $potonganKasbon, float $gajiBersih): Jurnal
    {
        if ($ada = $this->sudahAda(PeriodeGaji::class, $periode->id, 'gaji_disetujui')) {
            return $ada;
        }

        $baris = [
            ['akun_id' => $this->akun('6-1100'), 'debit' => $totalPendapatan, 'kredit' => 0],
            ['akun_id' => $this->akun('2-1200'), 'debit' => 0, 'kredit' => $gajiBersih],
        ];
        if ($potonganKasbon > 0) {
            $baris[] = ['akun_id' => $this->akun('1-1500'), 'debit' => 0, 'kredit' => $potonganKasbon];
        }

        return (new JurnalService())->buat([
            'cabang_id' => $periode->cabang_id,
            'tanggal' => now()->toDateString(),
            'keterangan' => "Beban Gaji Periode {$periode->bulan}/{$periode->tahun}",
            'jenis' => 'otomatis',
            'peristiwa' => 'gaji_disetujui',
            'sumber_type' => PeriodeGaji::class,
            'sumber_id' => $periode->id,
        ], $baris, true);
    }

    public function catatGajiDibayar(PeriodeGaji $periode, float $totalGajiBersih): Jurnal
    {
        if ($ada = $this->sudahAda(PeriodeGaji::class, $periode->id, 'gaji_dibayar')) {
            return $ada;
        }

        return (new JurnalService())->buat([
            'cabang_id' => $periode->cabang_id,
            'tanggal' => $periode->tanggal_bayar ?? now()->toDateString(),
            'keterangan' => "Pembayaran Gaji Periode {$periode->bulan}/{$periode->tahun}",
            'jenis' => 'otomatis',
            'peristiwa' => 'gaji_dibayar',
            'sumber_type' => PeriodeGaji::class,
            'sumber_id' => $periode->id,
        ], [
            ['akun_id' => $this->akun('2-1200'), 'debit' => $totalGajiBersih, 'kredit' => 0],
            ['akun_id' => $periode->akun_kas_id ?: $this->akun('1-1100'), 'debit' => 0, 'kredit' => $totalGajiBersih],
        ], true);
    }
}
