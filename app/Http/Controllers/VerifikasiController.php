<?php

namespace App\Http\Controllers;

use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Kendaraan;
use App\Services\JurnalOtomatisService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Verifikasi input lapangan dari sopir (MODUL-SOPIR-GPS 4.3).
 * Hanya data terverifikasi yang masuk laporan & perhitungan.
 */
class VerifikasiController extends Controller
{
    public function index()
    {
        $bbmLogs = BbmLog::with(['cabang', 'kendaraan', 'sopir'])
            ->where('status_verifikasi', 'menunggu_verifikasi')
            ->orderBy('tanggal')
            ->get();

        $biayas = BiayaOperasional::with(['cabang', 'kendaraan'])
            ->where('status_verifikasi', 'menunggu_verifikasi')
            ->orderBy('tanggal')
            ->get();

        return view('verifikasi.index', compact('bbmLogs', 'biayas'));
    }

    public function bbm(Request $request, BbmLog $bbmLog)
    {
        $validated = $request->validate([
            'keputusan' => 'required|in:setujui,tolak',
            'alasan_tolak' => 'required_if:keputusan,tolak|nullable|string|max:300',
            'liter' => 'nullable|numeric|min:0.1',
            'harga_per_liter' => 'nullable|numeric|min:1',
            'odometer' => 'nullable|integer|min:0',
        ], ['alasan_tolak.required_if' => 'Alasan penolakan wajib diisi.']);

        if ($bbmLog->status_verifikasi !== 'menunggu_verifikasi') {
            return back()->withErrors(['verifikasi' => 'Data ini sudah diverifikasi sebelumnya.']);
        }

        DB::transaction(function () use ($request, $validated, $bbmLog) {
            if ($validated['keputusan'] === 'tolak') {
                $bbmLog->update([
                    'status_verifikasi' => 'ditolak',
                    'alasan_tolak' => $validated['alasan_tolak'],
                    'diverifikasi_oleh' => $request->user()->id,
                    'diverifikasi_pada' => now(),
                ]);

                return;
            }

            // Admin boleh mengoreksi nilai yang keliru sebelum menyetujui.
            $liter = (float) ($validated['liter'] ?? $bbmLog->liter);
            $harga = (float) ($validated['harga_per_liter'] ?? $bbmLog->harga_per_liter);
            $odometer = (int) ($validated['odometer'] ?? $bbmLog->odometer);

            $bbmLog->update([
                'liter' => $liter,
                'harga_per_liter' => $harga,
                'total' => round($liter * $harga, 2),
                'odometer' => $odometer,
                'status_verifikasi' => 'terverifikasi',
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
            ]);

            $kendaraan = Kendaraan::lockForUpdate()->find($bbmLog->kendaraan_id);
            if ($kendaraan && $odometer > (int) $kendaraan->odometer_terakhir) {
                $kendaraan->update(['odometer_terakhir' => $odometer]);
            }

            (new JurnalOtomatisService())->catatBbmTerverifikasi($bbmLog->fresh());
        });

        return back()->with('success', 'Verifikasi BBM disimpan.');
    }

    public function biaya(Request $request, BiayaOperasional $biaya)
    {
        $validated = $request->validate([
            'keputusan' => 'required|in:setujui,tolak',
            'alasan_tolak' => 'required_if:keputusan,tolak|nullable|string|max:300',
            'jumlah' => 'nullable|numeric|min:1',
        ], ['alasan_tolak.required_if' => 'Alasan penolakan wajib diisi.']);

        if ($biaya->status_verifikasi !== 'menunggu_verifikasi') {
            return back()->withErrors(['verifikasi' => 'Data ini sudah diverifikasi sebelumnya.']);
        }

        DB::transaction(function () use ($request, $validated, $biaya) {
            $biaya->update([
                'jumlah' => $validated['keputusan'] === 'setujui' ? ($validated['jumlah'] ?? $biaya->jumlah) : $biaya->jumlah,
                'status_verifikasi' => $validated['keputusan'] === 'setujui' ? 'terverifikasi' : 'ditolak',
                'alasan_tolak' => $validated['keputusan'] === 'tolak' ? $validated['alasan_tolak'] : null,
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
            ]);

            if ($validated['keputusan'] === 'setujui') {
                (new JurnalOtomatisService())->catatBiayaOperasional($biaya->fresh());
            }
        });

        return back()->with('success', 'Verifikasi biaya jalan disimpan.');
    }
}
