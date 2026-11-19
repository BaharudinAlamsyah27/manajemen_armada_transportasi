<?php

namespace App\Http\Controllers;

use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Models\ServisKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->roles->first()->name ?? 'Guest';
        $isPusat = $user->hasAnyRole(['admin_pusat', 'keuangan']);
        $filterCabangId = $isPusat && $request->filled('cabang_id') ? (int) $request->input('cabang_id') : null;

        $cabangs = $isPusat ? Cabang::where('aktif', true)->orderBy('nama')->get() : collect();
        $cabangNama = $filterCabangId
            ? ($cabangs->firstWhere('id', $filterCabangId)?->nama ?? 'Cabang')
            : ($user->cabang ? $user->cabang->nama : 'Pusat (Semua Cabang)');

        $batasDokumen = now()->addDays(30)->toDateString();

        $dokumenKendaraanHabis = Kendaraan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->where(function ($q) use ($batasDokumen) {
                $q->whereDate('stnk_berlaku_sampai', '<=', $batasDokumen)
                  ->orWhereDate('kir_berlaku_sampai', '<=', $batasDokumen)
                  ->orWhereDate('pajak_berlaku_sampai', '<=', $batasDokumen);
            })->count();

        $simHabis = Karyawan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->where('jabatan', 'sopir')
            ->whereNotNull('sim_berlaku_sampai')
            ->whereDate('sim_berlaku_sampai', '<=', $batasDokumen)
            ->count();

        $awalBulan = now()->startOfMonth()->toDateString();
        $biayaBbm = (float) BbmLog::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->where('status_verifikasi', 'terverifikasi')
            ->whereDate('tanggal', '>=', $awalBulan)
            ->sum('total');
        $biayaServis = (float) ServisKendaraan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->where('status', 'selesai')
            ->whereDate('tanggal_selesai', '>=', $awalBulan)
            ->sum('total_biaya');
        $biayaOps = (float) BiayaOperasional::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->where('status_verifikasi', 'terverifikasi')
            ->whereDate('tanggal', '>=', $awalBulan)
            ->sum('jumlah');

        $stats = [
            'total_pesanan' => Pesanan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->count(),
            'pesanan_menunggu' => Pesanan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->where('status', 'menunggu_jadwal')->count(),
            'pengiriman_aktif' => Pengiriman::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->whereIn('status', ['terjadwal', 'berjalan'])->count(),
            'kendaraan_tersedia' => Kendaraan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->where('status', 'tersedia')->count(),
            'kendaraan_bertugas' => Kendaraan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->where('status', 'bertugas')->count(),
            'kendaraan_servis' => Kendaraan::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))->where('status', 'servis')->count(),
            'dokumen_peringatan' => $dokumenKendaraanHabis + $simHabis,
            'biaya_bulan_ini' => $biayaBbm + $biayaServis + $biayaOps,
        ];

        // Grafik 1: Pengiriman 7 hari terakhir
        $chartLabels = [];
        $chartPengiriman = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = now()->subDays($i);
            $chartLabels[] = $tgl->format('d M');
            $chartPengiriman[] = Pengiriman::when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
                ->whereDate('tanggal_rencana', $tgl->toDateString())
                ->where('status', '!=', 'batal')
                ->count();
        }

        // Grafik 2: Komposisi biaya armada bulan ini
        $chartBiaya = [
            'BBM' => $biayaBbm,
            'Servis & Suku Cadang' => $biayaServis,
            'Tol, Parkir & Lainnya' => $biayaOps,
        ];

        $pengirimanBerjalan = Pengiriman::with(['kendaraan', 'sopir', 'cabang'])
            ->when($filterCabangId, fn ($q) => $q->where('cabang_id', $filterCabangId))
            ->whereIn('status', ['terjadwal', 'berjalan'])
            ->latest('tanggal_rencana')
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'user' => $user,
            'role' => $role,
            'cabang' => $cabangNama,
            'cabangs' => $cabangs,
            'isPusat' => $isPusat,
            'filterCabangId' => $filterCabangId,
            'stats' => $stats,
            'chartLabels' => $chartLabels,
            'chartPengiriman' => $chartPengiriman,
            'chartBiaya' => $chartBiaya,
            'pengirimanBerjalan' => $pengirimanBerjalan,
        ]);
    }
}
