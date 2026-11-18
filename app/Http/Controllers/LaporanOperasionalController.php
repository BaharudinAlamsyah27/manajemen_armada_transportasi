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

        return view('laporan.operasional', compact('dari', 'sampai', 'data'));
    }

    public function riwayat()
    {
        $logs = Activity::with('causer')->latest()->limit(50)->get();

        return view('laporan.riwayat', compact('logs'));
    }
}
