<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $karyawans = Karyawan::with('cabang')->where('aktif', true)->orderBy('nama')->get();

        $absensiHariIni = Absensi::whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('karyawan_id');

        $bulan = substr($tanggal, 0, 7);
        $rekapBulan = Absensi::where('tanggal', 'like', "{$bulan}%")
            ->select('karyawan_id', 'status', DB::raw('count(*) as total'))
            ->groupBy('karyawan_id', 'status')
            ->get()
            ->groupBy('karyawan_id');

        return view('absensi.index', compact('tanggal', 'karyawans', 'absensiHariIni', 'rekapBulan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:hadir,izin,sakit,alpa,libur',
            'catatan' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $tanggal = $validated['tanggal'];

        DB::transaction(function () use ($validated, $tanggal, $userId) {
            foreach ($validated['status'] as $karyawanId => $status) {
                $karyawan = Karyawan::find($karyawanId);
                if (!$karyawan) {
                    continue;
                }

                Absensi::updateOrCreate(
                    ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal],
                    [
                        'cabang_id' => $karyawan->cabang_id,
                        'status' => $status,
                        'catatan' => $validated['catatan'][$karyawanId] ?? null,
                        'dicatat_oleh' => $userId,
                    ]
                );
            }
        });

        return redirect()->route('absensi.index', ['tanggal' => $tanggal])
            ->with('success', 'Absensi tanggal ' . $tanggal . ' berhasil disimpan.');
    }
}
