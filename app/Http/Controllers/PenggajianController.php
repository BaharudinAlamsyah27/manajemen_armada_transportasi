<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Cabang;
use App\Models\PeriodeGaji;
use App\Services\PayrollService;
use Exception;
use Illuminate\Http\Request;

class PenggajianController extends Controller
{
    public function index()
    {
        $periodes = PeriodeGaji::withoutGlobalScopes()->with(['slips.karyawan'])->latest()->get();
        $cabangs = Cabang::where('aktif', true)->get();
        $akunKas = Akun::where('is_kas', true)->where('aktif', true)->get();

        return view('penggajian.index', compact('periodes', 'cabangs', 'akunKas'));
    }

    public function hitung(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        try {
            (new PayrollService())->hitungPeriode(
                (int) $validated['cabang_id'],
                (int) $validated['tahun'],
                (int) $validated['bulan']
            );
            return back()->with('success', 'Slip gaji periode berhasil dihitung.');
        } catch (Exception $e) {
            return back()->withErrors(['gaji' => $e->getMessage()]);
        }
    }

    public function setujui(PeriodeGaji $periode)
    {
        try {
            (new PayrollService())->setujui($periode);
            return back()->with('success', 'Periode gaji disetujui dan jurnal beban gaji diposting.');
        } catch (Exception $e) {
            return back()->withErrors(['gaji' => $e->getMessage()]);
        }
    }

    public function bayar(Request $request, PeriodeGaji $periode)
    {
        $validated = $request->validate(['akun_kas_id' => 'required|exists:akuns,id']);
        try {
            (new PayrollService())->bayarDanPublikasikan($periode, (int) $validated['akun_kas_id'], now()->toDateString());
            return back()->with('success', 'Gaji dibayar & slip dipublikasikan ke portal sopir.');
        } catch (Exception $e) {
            return back()->withErrors(['gaji' => $e->getMessage()]);
        }
    }
}
