<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Kendaraan;
use App\Models\ServisKendaraan;
use App\Models\StokSukuCadang;
use App\Models\SukuCadang;
use App\Services\StokSukuCadangService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServisController extends Controller
{
    public function index()
    {
        $servisList = ServisKendaraan::with(['cabang', 'kendaraan'])->latest('tanggal_masuk')->get();
        $kendaraans = Kendaraan::orderBy('kode')->get();
        $sukuCadangs = SukuCadang::orderBy('kode')->get();
        $stokCabang = StokSukuCadang::with(['cabang', 'sukuCadang'])->get();
        $cabangs = Cabang::where('aktif', true)->get();

        return view('servis.index', compact('servisList', 'kendaraans', 'sukuCadangs', 'stokCabang', 'cabangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'jenis' => 'required|in:berkala,perbaikan,ganti_ban,kir,lainnya',
            'tanggal_masuk' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'bengkel' => 'nullable|string|max:100',
            'biaya_jasa' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $kendaraan = Kendaraan::findOrFail($validated['kendaraan_id']);
        if ($kendaraan->status === 'bertugas') {
            return back()->withErrors(['kendaraan_id' => 'Kendaraan sedang bertugas dan tidak dapat masuk servis.']);
        }

        ServisKendaraan::create([
            'cabang_id' => $kendaraan->cabang_id,
            'kendaraan_id' => $kendaraan->id,
            'jenis' => $validated['jenis'],
            'status' => 'dikerjakan',
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'odometer' => $validated['odometer'],
            'bengkel' => $validated['bengkel'] ?? null,
            'biaya_jasa' => $validated['biaya_jasa'],
            'total_biaya' => $validated['biaya_jasa'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        $kendaraan->update(['status' => 'servis']);

        return redirect()->route('servis.index')->with('success', 'Data servis dicatat dan status kendaraan diubah ke Servis.');
    }

    public function selesai(ServisKendaraan $servis)
    {
        $servis->update([
            'status' => 'selesai',
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $masihServis = ServisKendaraan::where('kendaraan_id', $servis->kendaraan_id)
            ->where('status', 'dikerjakan')
            ->exists();

        if (!$masihServis && $servis->kendaraan) {
            $servis->kendaraan->update(['status' => 'tersedia']);
        }

        return redirect()->route('servis.index')->with('success', 'Servis selesai, kendaraan kembali Tersedia.');
    }

    public function mutasiStok(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'cabang_id' => $user->hasRole('admin_pusat') ? 'required|exists:cabangs,id' : 'nullable|exists:cabangs,id',
            'suku_cadang_id' => 'required|exists:suku_cadangs,id',
            'tipe' => 'required|in:masuk,keluar_servis,penyesuaian',
            'qty' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:200',
        ]);

        $cabangId = $user->hasRole('admin_pusat') ? (int) $validated['cabang_id'] : (int) $user->cabang_id;

        try {
            $service = new StokSukuCadangService();
            $service->mutasi(
                $cabangId,
                (int) $validated['suku_cadang_id'],
                $validated['tipe'],
                (int) $validated['qty'],
                null,
                $validated['keterangan'] ?? null
            );
            return redirect()->route('servis.index')->with('success', 'Mutasi stok suku cadang berhasil dicatat.');
        } catch (Exception $e) {
            return back()->withErrors(['stok' => $e->getMessage()]);
        }
    }
}
