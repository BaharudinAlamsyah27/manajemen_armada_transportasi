<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\PeriodeAkuntansi;
use App\Services\JurnalService;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $cabangId = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;
        $svc = new LaporanKeuanganService();

        $jurnals = Jurnal::with(['details.akun'])->latest('tanggal')->limit(30)->get();
        $akuns = Akun::where('aktif', true)->orderBy('kode')->get();
        $neracaSaldo = $svc->neracaSaldo($cabangId);
        $labaRugi = $svc->labaRugi($cabangId);
        $neraca = $svc->neraca($cabangId);
        $rekonsiliasi = $svc->rekonsiliasi($cabangId);
        $periodes = PeriodeAkuntansi::orderByDesc('tahun')->orderByDesc('bulan')->get();

        return view('keuangan.index', compact(
            'jurnals', 'akuns', 'neracaSaldo', 'labaRugi', 'neraca', 'rekonsiliasi', 'periodes'
        ));
    }

    public function storeJurnal(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:300',
            'akun_debit' => 'required|exists:akuns,id',
            'akun_kredit' => 'required|exists:akuns,id|different:akun_debit',
            'nominal' => 'required|numeric|min:1',
        ]);

        try {
            (new JurnalService())->buat([
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'jenis' => 'manual',
            ], [
                ['akun_id' => $validated['akun_debit'], 'debit' => $validated['nominal'], 'kredit' => 0],
                ['akun_id' => $validated['akun_kredit'], 'debit' => 0, 'kredit' => $validated['nominal']],
            ], false);

            return redirect()->route('keuangan.index')->with('success', 'Jurnal draf berhasil dibuat (menunggu posting pemeriksa).');
        } catch (Exception $e) {
            return back()->withErrors(['jurnal' => $e->getMessage()]);
        }
    }

    public function posting(Jurnal $jurnal)
    {
        try {
            (new JurnalService())->posting($jurnal);
            return back()->with('success', "Jurnal {$jurnal->no_jurnal} berhasil diposting.");
        } catch (Exception $e) {
            return back()->withErrors(['jurnal' => $e->getMessage()]);
        }
    }

    public function balik(Request $request, Jurnal $jurnal)
    {
        $validated = $request->validate(['alasan' => 'required|string|max:200']);
        try {
            (new JurnalService())->balik($jurnal, $validated['alasan']);
            return back()->with('success', "Jurnal balik untuk {$jurnal->no_jurnal} berhasil dibuat.");
        } catch (Exception $e) {
            return back()->withErrors(['jurnal' => $e->getMessage()]);
        }
    }

    public function tutupPeriode(PeriodeAkuntansi $periode)
    {
        if (Jurnal::withoutGlobalScopes()->where('periode_id', $periode->id)->where('status', 'draf')->exists()) {
            return back()->withErrors(['periode' => 'Masih ada jurnal berstatus draf pada periode ini.']);
        }

        $periode->update([
            'status' => 'tutup',
            'ditutup_oleh' => Auth::id(),
            'ditutup_pada' => now(),
        ]);

        return back()->with('success', "Periode {$periode->bulan}/{$periode->tahun} berhasil ditutup.");
    }

    public function pdf()
    {
        $svc = new LaporanKeuanganService();
        $data = [
            'labaRugi' => $svc->labaRugi(),
            'neraca' => $svc->neraca(),
            'rekonsiliasi' => $svc->rekonsiliasi(),
        ];

        $pdf = Pdf::loadView('keuangan.pdf', $data);
        return $pdf->download('laporan-keuangan-fleetflow.pdf');
    }
}
