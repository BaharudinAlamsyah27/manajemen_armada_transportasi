<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Invoice;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\PeriodeAkuntansi;
use App\Models\TagihanPemasok;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganService
{
    public function neracaSaldo(?int $cabangId = null, ?string $sampaiTanggal = null): array
    {
        $akuns = Akun::orderBy('kode')->get();
        $query = JurnalDetail::query()
            ->join('jurnals', 'jurnals.id', '=', 'jurnal_details.jurnal_id')
            ->where('jurnals.status', 'posted')
            ->whereNull('jurnals.deleted_at');

        if ($cabangId) {
            $query->where('jurnals.cabang_id', $cabangId);
        }
        if ($sampaiTanggal) {
            $query->whereDate('jurnals.tanggal', '<=', $sampaiTanggal);
        }

        $mutasi = $query->select(
            'jurnal_details.akun_id',
            DB::raw('SUM(jurnal_details.debit) as total_debit'),
            DB::raw('SUM(jurnal_details.kredit) as total_kredit')
        )->groupBy('jurnal_details.akun_id')->get()->keyBy('akun_id');

        $rows = [];
        $sumDebit = 0.0;
        $sumKredit = 0.0;

        foreach ($akuns as $akun) {
            $m = $mutasi->get($akun->id);
            $d = (float) ($m->total_debit ?? 0);
            $k = (float) ($m->total_kredit ?? 0);
            $saldo = $akun->saldo_normal === 'debit' ? ($d - $k) : ($k - $d);

            $sumDebit += $d;
            $sumKredit += $k;

            $rows[] = [
                'akun' => $akun,
                'debit' => $d,
                'kredit' => $k,
                'saldo' => $saldo,
            ];
        }

        return [
            'rows' => $rows,
            'total_debit' => round($sumDebit, 2),
            'total_kredit' => round($sumKredit, 2),
            'seimbang' => bccomp((string) round($sumDebit, 2), (string) round($sumKredit, 2), 2) === 0,
        ];
    }

    public function labaRugi(?int $cabangId = null, ?string $sampaiTanggal = null): array
    {
        $ns = $this->neracaSaldo($cabangId, $sampaiTanggal);
        $pendapatan = 0.0;
        $bebanPokok = 0.0;
        $bebanUsaha = 0.0;

        foreach ($ns['rows'] as $row) {
            $akun = $row['akun'];
            if ($akun->tipe === 'pendapatan') {
                $pendapatan += $row['saldo'];
            } elseif ($akun->tipe === 'beban') {
                if (str_starts_with($akun->kode, '5-')) {
                    $bebanPokok += $row['saldo'];
                } else {
                    $bebanUsaha += $row['saldo'];
                }
            }
        }

        $labaKotor = round($pendapatan - $bebanPokok, 2);
        $labaBersih = round($labaKotor - $bebanUsaha, 2);

        return [
            'pendapatan' => round($pendapatan, 2),
            'beban_pokok' => round($bebanPokok, 2),
            'laba_kotor' => $labaKotor,
            'beban_usaha' => round($bebanUsaha, 2),
            'laba_bersih' => $labaBersih,
        ];
    }

    public function neraca(?int $cabangId = null, ?string $sampaiTanggal = null): array
    {
        $ns = $this->neracaSaldo($cabangId, $sampaiTanggal);
        $lr = $this->labaRugi($cabangId, $sampaiTanggal);

        $aset = 0.0;
        $liabilitas = 0.0;
        $ekuitas = 0.0;

        foreach ($ns['rows'] as $row) {
            $akun = $row['akun'];
            if ($akun->tipe === 'aset') {
                // Kontra aset (saldo normal kredit, mis. akumulasi penyusutan) mengurangi total aset
                $aset += $akun->saldo_normal === 'debit' ? $row['saldo'] : -$row['saldo'];
            } elseif ($akun->tipe === 'liabilitas') {
                $liabilitas += $row['saldo'];
            } elseif ($akun->tipe === 'ekuitas') {
                $ekuitas += $row['saldo'];
            }
        }

        $ekuitasDenganLaba = round($ekuitas + $lr['laba_bersih'], 2);
        $pasiva = round($liabilitas + $ekuitasDenganLaba, 2);

        return [
            'aset' => round($aset, 2),
            'liabilitas' => round($liabilitas, 2),
            'ekuitas' => round($ekuitas, 2),
            'laba_berjalan' => $lr['laba_bersih'],
            'total_pasiva' => $pasiva,
            'seimbang' => bccomp((string) round($aset, 2), (string) $pasiva, 2) === 0,
        ];
    }

    public function rekonsiliasi(?int $cabangId = null): array
    {
        $ns = $this->neracaSaldo($cabangId);
        $neraca = $this->neraca($cabangId);

        $saldoAkun = fn (string $kode): float => collect($ns['rows'])
            ->first(fn ($r) => $r['akun']->kode === $kode)['saldo'] ?? 0.0;

        $subledgerPiutang = (float) Invoice::withoutGlobalScopes()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->whereNotIn('status', ['batal', 'draf'])
            ->selectRaw('COALESCE(SUM(total - terbayar), 0) as sisa')
            ->value('sisa');

        $subledgerHutang = (float) TagihanPemasok::withoutGlobalScopes()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('status', '!=', 'batal')
            ->selectRaw('COALESCE(SUM(jumlah - terbayar), 0) as sisa')
            ->value('sisa');

        return [
            'jurnal_seimbang' => $ns['seimbang'],
            'neraca_seimbang' => $neraca['seimbang'],
            'piutang_subledger' => round($subledgerPiutang, 2),
            'piutang_akun' => round($saldoAkun('1-1300'), 2),
            'hutang_subledger' => round($subledgerHutang, 2),
            'hutang_akun' => round($saldoAkun('2-1100'), 2),
            'kas_bank_total' => round($saldoAkun('1-1100') + $saldoAkun('1-1200'), 2),
            'periode_aktif' => PeriodeAkuntansi::orderByDesc('tahun')->orderByDesc('bulan')->first(),
        ];
    }
}
