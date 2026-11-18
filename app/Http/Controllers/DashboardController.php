<?php

namespace App\Http\Controllers;

use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\ServisKendaraan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first()->name ?? 'Guest';
        $cabang = $user->cabang ? $user->cabang->nama : 'Pusat (Semua Cabang)';

        $batasDokumen = now()->addDays(30)->toDateString();

        $dokumenKendaraanHabis = Kendaraan::where(function ($q) use ($batasDokumen) {
            $q->whereDate('stnk_berlaku_sampai', '<=', $batasDokumen)
              ->orWhereDate('kir_berlaku_sampai', '<=', $batasDokumen)
              ->orWhereDate('pajak_berlaku_sampai', '<=', $batasDokumen);
        })->count();

        $simHabis = Karyawan::where('jabatan', 'sopir')
            ->whereNotNull('sim_berlaku_sampai')
            ->whereDate('sim_berlaku_sampai', '<=', $batasDokumen)
            ->count();

        $awalBulan = now()->startOfMonth()->toDateString();
        $biayaBbm = (float) BbmLog::where('status_verifikasi', 'terverifikasi')
            ->whereDate('tanggal', '>=', $awalBulan)
            ->sum('total');
        $biayaServis = (float) ServisKendaraan::where('status', 'selesai')
            ->whereDate('tanggal_selesai', '>=', $awalBulan)
            ->sum('total_biaya');
        $biayaOps = (float) BiayaOperasional::where('status_verifikasi', 'terverifikasi')
            ->whereDate('tanggal', '>=', $awalBulan)
            ->sum('jumlah');

        $stats = [
            'total_pesanan' => Pesanan::count(),
            'pesanan_menunggu' => Pesanan::where('status', 'menunggu_jadwal')->count(),
            'pengiriman_aktif' => Pengiriman::whereIn('status', ['terjadwal', 'berjalan'])->count(),
            'kendaraan_tersedia' => Kendaraan::where('status', 'tersedia')->count(),
            'kendaraan_bertugas' => Kendaraan::where('status', 'bertugas')->count(),
            'kendaraan_servis' => Kendaraan::where('status', 'servis')->count(),
            'dokumen_peringatan' => $dokumenKendaraanHabis + $simHabis,
            'biaya_bulan_ini' => $biayaBbm + $biayaServis + $biayaOps,
        ];

        $pengirimanBerjalan = Pengiriman::with(['kendaraan', 'sopir', 'cabang'])
            ->whereIn('status', ['terjadwal', 'berjalan'])
            ->latest('tanggal_rencana')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('user', 'role', 'cabang', 'stats', 'pengirimanBerjalan'));
    }
}
