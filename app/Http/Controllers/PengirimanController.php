<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pesanan;
use App\Services\PengirimanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::with(['cabang', 'kendaraan', 'sopir', 'pesanans']);
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where('kode', 'like', "%{$q}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $pengirimans = $query->latest()->get();
        return view('pengiriman.index', compact('pengirimans'));
    }

    public function create()
    {
        $kendaraans = Kendaraan::where('status', 'tersedia')->orderBy('kode')->get();
        $sopirs = Karyawan::where('jabatan', 'sopir')->where('aktif', true)->orderBy('nama')->get();
        $pesanans = Pesanan::where('status', 'menunggu_jadwal')->orderBy('id')->get();
        return view('pengiriman.create', compact('kendaraans', 'sopirs', 'pesanans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_rencana' => 'required|date',
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'sopir_id' => 'required|exists:karyawans,id',
            'tujuan_ringkas' => 'nullable|string|max:150',
            'pesanan_ids' => 'required|array|min:1',
            'pesanan_ids.*' => 'exists:pesanans,id',
            'catatan' => 'nullable|string',
            'override_alasan' => 'nullable|string',
        ]);

        try {
            $service = new PengirimanService();
            $pengiriman = $service->createManifest($validated, $validated['pesanan_ids']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', "Manifest {$pengiriman->kode} berhasil dijadwalkan.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['manifest' => $e->getMessage()]);
        }
    }

    public function show(Pengiriman $pengiriman)
    {
        $pengiriman->load(['cabang', 'kendaraan', 'sopir', 'pesanans']);
        return view('pengiriman.show', compact('pengiriman'));
    }

    public function mulai(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            'odometer_awal' => 'required|integer|min:0',
        ]);

        try {
            $service = new PengirimanService();
            $service->mulaiPerjalanan($pengiriman, (int) $validated['odometer_awal']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman berhasil diberangkatkan.');
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    public function selesai(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate([
            'odometer_akhir' => 'required|integer|min:0',
        ]);

        try {
            $service = new PengirimanService();
            $service->selesaiPerjalanan($pengiriman, (int) $validated['odometer_akhir']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman telah diselesaikan.');
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    public function batal(Request $request, Pengiriman $pengiriman)
    {
        $validated = $request->validate(['alasan' => 'required|string|max:300']);

        try {
            (new PengirimanService())->batal($pengiriman, $validated['alasan']);
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman dibatalkan; pesanan kembali menunggu jadwal.');
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    /**
     * Admin menandai pesanan terkirim; foto opsional, tanpa foto wajib catatan (A19).
     */
    public function terkirim(Request $request, Pengiriman $pengiriman, Pesanan $pesanan)
    {
        abort_unless((int) $pesanan->pengiriman_id === (int) $pengiriman->id, 404);

        $validated = $request->validate([
            'nama_penerima' => 'required|string|max:100',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            (new PengirimanService())->tandaiTerkirim(
                $pesanan,
                $validated['nama_penerima'],
                $request->file('foto'),
                $validated['catatan'] ?? null,
                'admin'
            );
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', "Pesanan {$pesanan->kode_resi} ditandai terkirim.");
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    public function gagal(Request $request, Pengiriman $pengiriman, Pesanan $pesanan)
    {
        abort_unless((int) $pesanan->pengiriman_id === (int) $pengiriman->id, 404);

        $validated = $request->validate(['alasan' => 'required|string|max:300']);

        try {
            (new PengirimanService())->tandaiGagal($pesanan, $validated['alasan'], 'admin');
            return redirect()->route('pengiriman.show', $pengiriman)->with('success', "Pesanan {$pesanan->kode_resi} ditandai gagal kirim.");
        } catch (Exception $e) {
            return back()->withErrors(['aksi' => $e->getMessage()]);
        }
    }

    public function cetakSuratJalan(Pengiriman $pengiriman)
    {
        $pengiriman->load(['cabang', 'kendaraan', 'sopir', 'pesanans']);
        $pdf = Pdf::loadView('pengiriman.surat-jalan-pdf', compact('pengiriman'));

        return $pdf->download("SuratJalan-{$pengiriman->kode}.pdf");
    }
}
