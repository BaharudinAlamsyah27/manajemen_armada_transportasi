<?php

namespace App\Http\Controllers;

use App\Services\LaporanOperasionalService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LaporanOperasionalController extends Controller
{
    public function index(Request $request)
    {
        $dari = $request->input('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());

        $data = (new LaporanOperasionalService())->ringkasanPerKendaraan($dari, $sampai);

        if ($request->query('export') === 'csv') {
            $csv = "Plat Nomor,Cabang,Jumlah Pengiriman,KM Tempuh,BBM (Rp),Servis (Rp),Operasional (Rp),Total Biaya (Rp),Biaya per KM (Rp)\n";
            foreach ($data['per_kendaraan'] as $r) {
                $csv .= sprintf(
                    "\"%s\",\"%s\",%d,%d,%.2f,%.2f,%.2f,%.2f,%s\n",
                    $r['kendaraan']->plat_nomor,
                    $r['kendaraan']->cabang?->nama ?? '-',
                    $r['jumlah_pengiriman'],
                    $r['km_tempuh'],
                    $r['bbm'],
                    $r['servis'],
                    $r['operasional'],
                    $r['total_biaya'],
                    $r['biaya_per_km'] ?? 0
                );
            }
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"laporan-armada-{$dari}-{$sampai}.csv\"",
            ]);
        }

        return view('laporan.operasional', compact('dari', 'sampai', 'data'));
    }

    public function riwayat()
    {
        $logs = Activity::with('causer')->latest()->limit(50)->get();

        return view('laporan.riwayat', compact('logs'));
    }
}
