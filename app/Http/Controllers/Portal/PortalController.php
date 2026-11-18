<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\Pengumuman;
use App\Models\PengumumanDibaca;
use App\Models\Pesanan;
use App\Models\SlipGaji;
use App\Services\PengirimanService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Portal sopir (MODUL-SOPIR-GPS bagian 4). Semua query dibatasi karyawan_id
 * pengguna, bukan hanya cabang. Objek milik sopir lain menghasilkan 404.
 */
class PortalController extends Controller
{
    private function karyawanId(Request $request): int
    {
        return (int) $request->user()->karyawan_id;
    }

    private function pengirimanSaya(Request $request): Builder
    {
        return Pengiriman::where('sopir_id', $this->karyawanId($request));
    }

    private function cariPengirimanSaya(Request $request, int $id): Pengiriman
    {
        return $this->pengirimanSaya($request)->findOrFail($id);
    }

    private function cariPesananSaya(Request $request, int $pengirimanId, int $pesananId): Pesanan
    {
        $pengiriman = $this->cariPengirimanSaya($request, $pengirimanId);

        return $pengiriman->pesanans()->findOrFail($pesananId);
    }

    public function beranda(Request $request)
    {
        $karyawan = Karyawan::findOrFail($this->karyawanId($request));

        $hariIni = $this->pengirimanSaya($request)
            ->with('kendaraan')
            ->withCount('pesanans')
            ->whereDate('tanggal_rencana', today())
            ->whereIn('status', ['terjadwal', 'berjalan'])
            ->orderBy('waktu_mulai_rencana')
            ->get();

        $simSegeraHabis = $karyawan->sim_berlaku_sampai
            && $karyawan->sim_berlaku_sampai->lte(now()->addDays(30));

        return view('portal.beranda', compact('karyawan', 'hariIni', 'simSegeraHabis'));
    }

    public function jadwal(Request $request)
    {
        $mendatang = $this->pengirimanSaya($request)
            ->with('kendaraan')
            ->withCount('pesanans')
            ->whereDate('tanggal_rencana', '>=', today())
            ->whereDate('tanggal_rencana', '<=', today()->addDays(7))
            ->orderBy('tanggal_rencana')
            ->get();

        $riwayat = $this->pengirimanSaya($request)
            ->with('kendaraan')
            ->withCount('pesanans')
            ->whereDate('tanggal_rencana', '<', today())
            ->whereDate('tanggal_rencana', '>=', today()->subDays(30))
            ->orderByDesc('tanggal_rencana')
            ->paginate(10);

        return view('portal.jadwal', compact('mendatang', 'riwayat'));
    }

    public function pengiriman(Request $request, int $pengiriman)
    {
        $pengiriman = $this->cariPengirimanSaya($request, $pengiriman);
        $pengiriman->load(['kendaraan', 'pesanans.pengiriman']);

        return view('portal.pengiriman', compact('pengiriman'));
    }

    public function mulai(Request $request, int $pengiriman)
    {
        $pengiriman = $this->cariPengirimanSaya($request, $pengiriman);
        $validated = $request->validate([
            'odometer_awal' => 'required|integer|min:0',
        ]);

        return $this->jalankan($pengiriman, fn () => (new PengirimanService())
            ->mulaiPerjalanan($pengiriman, (int) $validated['odometer_awal'], 'sopir'), 'Perjalanan dimulai. Hati-hati di jalan!');
    }

    public function terkirim(Request $request, int $pengiriman, int $pesanan)
    {
        $pesanan = $this->cariPesananSaya($request, $pengiriman, $pesanan);
        $validated = $request->validate([
            'nama_penerima' => 'required|string|max:100',
            'foto' => 'required|file|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048',
            'catatan' => 'nullable|string|max:500',
        ], [], [
            'nama_penerima' => 'nama penerima',
            'foto' => 'foto bukti',
        ]);

        return $this->jalankan($pesanan->pengiriman, fn () => (new PengirimanService())->tandaiTerkirim(
            $pesanan,
            $validated['nama_penerima'],
            $request->file('foto'),
            $validated['catatan'] ?? null,
            'sopir'
        ), "Pesanan {$pesanan->kode_resi} ditandai terkirim.");
    }

    public function gagal(Request $request, int $pengiriman, int $pesanan)
    {
        $pesanan = $this->cariPesananSaya($request, $pengiriman, $pesanan);
        $validated = $request->validate([
            'alasan' => 'required|in:Penerima tidak ada,Alamat tidak ditemukan,Ditolak penerima,Barang rusak,Lainnya',
            'catatan' => 'nullable|string|max:500',
        ]);

        $alasan = $validated['alasan'] . (!empty($validated['catatan']) ? ' — ' . $validated['catatan'] : '');

        return $this->jalankan($pesanan->pengiriman, fn () => (new PengirimanService())
            ->tandaiGagal($pesanan, $alasan, 'sopir'), "Pesanan {$pesanan->kode_resi} ditandai gagal kirim.");
    }

    public function selesai(Request $request, int $pengiriman)
    {
        $pengiriman = $this->cariPengirimanSaya($request, $pengiriman);
        $validated = $request->validate([
            'odometer_akhir' => 'required|integer|min:0',
        ]);

        return $this->jalankan($pengiriman, fn () => (new PengirimanService())
            ->selesaiPerjalanan($pengiriman, (int) $validated['odometer_akhir'], 'sopir'), 'Pengiriman selesai. Terima kasih!');
    }

    private function jalankan(Pengiriman $pengiriman, callable $aksi, string $pesanSukses)
    {
        try {
            $aksi();
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['aksi' => $e->getMessage()]);
        }

        return redirect()->route('portal.pengiriman', $pengiriman->id)->with('success', $pesanSukses);
    }

    public function catatBbm(Request $request)
    {
        $kendaraans = $this->kendaraanSaya($request);

        return view('portal.catat-bbm', compact('kendaraans'));
    }

    public function simpanBbm(Request $request)
    {
        $validated = $request->validate([
            'kendaraan_id' => 'required|integer',
            'tanggal' => 'required|date|before_or_equal:today',
            'liter' => 'required|numeric|min:0.1|max:1000',
            'harga_per_liter' => 'required|numeric|min:1',
            'odometer' => 'required|integer|min:0',
            'tangki_penuh' => 'nullable|boolean',
            'spbu' => 'nullable|string|max:100',
            'foto' => 'required|file|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048',
        ], [], ['foto' => 'foto struk']);

        $kendaraan = $this->kendaraanSaya($request)->firstWhere('id', (int) $validated['kendaraan_id']);
        if (!$kendaraan) {
            return back()->withInput()->withErrors(['kendaraan_id' => 'Kendaraan tidak ditemukan pada jadwal Anda.']);
        }

        BbmLog::create([
            'cabang_id' => $kendaraan->cabang_id,
            'kendaraan_id' => $kendaraan->id,
            'sopir_id' => $this->karyawanId($request),
            'tanggal' => $validated['tanggal'],
            'liter' => $validated['liter'],
            'harga_per_liter' => $validated['harga_per_liter'],
            // total dihitung server (Pedoman 6.4)
            'total' => round((float) $validated['liter'] * (float) $validated['harga_per_liter'], 2),
            'odometer' => $validated['odometer'],
            'tangki_penuh' => $request->boolean('tangki_penuh'),
            'spbu' => $validated['spbu'] ?? null,
            'dicatat_oleh' => $request->user()->id,
            'sumber' => 'sopir',
            'status_verifikasi' => 'menunggu_verifikasi',
            'bukti_path' => $request->file('foto')->store('bukti-bbm', 'local'),
        ]);

        return redirect()->route('portal.beranda')->with('success', 'Catatan BBM terkirim dan menunggu verifikasi admin.');
    }

    public function catatBiaya(Request $request)
    {
        $kendaraans = $this->kendaraanSaya($request);

        return view('portal.catat-biaya', compact('kendaraans'));
    }

    public function simpanBiaya(Request $request)
    {
        $validated = $request->validate([
            'kendaraan_id' => 'required|integer',
            'jenis' => 'required|in:tol,parkir,lainnya',
            'tanggal' => 'required|date|before_or_equal:today',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:300',
            'foto' => 'required|file|mimes:jpg,jpeg,png|mimetypes:image/jpeg,image/png|max:2048',
        ], [], ['foto' => 'foto bukti']);

        $kendaraan = $this->kendaraanSaya($request)->firstWhere('id', (int) $validated['kendaraan_id']);
        if (!$kendaraan) {
            return back()->withInput()->withErrors(['kendaraan_id' => 'Kendaraan tidak ditemukan pada jadwal Anda.']);
        }

        BiayaOperasional::create([
            'cabang_id' => $kendaraan->cabang_id,
            'kendaraan_id' => $kendaraan->id,
            'jenis' => $validated['jenis'],
            'tanggal' => $validated['tanggal'],
            'jumlah' => $validated['jumlah'],
            'keterangan' => $validated['keterangan'] ?? null,
            'dicatat_oleh' => $request->user()->id,
            'sumber' => 'sopir',
            'status_verifikasi' => 'menunggu_verifikasi',
            'bukti_path' => $request->file('foto')->store('bukti-biaya', 'local'),
        ]);

        return redirect()->route('portal.beranda')->with('success', 'Biaya jalan terkirim dan menunggu verifikasi admin.');
    }

    /**
     * Kendaraan yang ditugaskan ke sopir dalam 7 hari terakhir s.d. hari ini.
     */
    private function kendaraanSaya(Request $request)
    {
        $ids = $this->pengirimanSaya($request)
            ->whereDate('tanggal_rencana', '>=', today()->subDays(7))
            ->whereDate('tanggal_rencana', '<=', today())
            ->pluck('kendaraan_id')
            ->unique();

        return Kendaraan::whereIn('id', $ids)->orderBy('plat_nomor')->get();
    }

    public function pengumuman(Request $request)
    {
        $user = $request->user();
        $hariIni = now()->toDateString();

        $pengumumans = Pengumuman::where(function ($q) use ($user) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
            })
            ->whereDate('terbit_pada', '<=', $hariIni)
            ->where(function ($q) use ($hariIni) {
                $q->whereNull('kedaluwarsa_pada')->orWhereDate('kedaluwarsa_pada', '>=', $hariIni);
            })
            ->orderByDesc('disematkan')
            ->latest('terbit_pada')
            ->get()
            ->filter(function (Pengumuman $p) {
                $peran = $p->target_peran ?? [];
                return empty($peran) || in_array('sopir', $peran, true);
            });

        foreach ($pengumumans as $p) {
            PengumumanDibaca::firstOrCreate(
                ['pengumuman_id' => $p->id, 'user_id' => $user->id],
                ['dibaca_pada' => now()]
            );
        }

        return view('portal.pengumuman', compact('pengumumans'));
    }

    public function slipGaji(Request $request)
    {
        $slips = SlipGaji::with(['periodeGaji', 'items'])
            ->where('karyawan_id', $this->karyawanId($request))
            ->whereHas('periodeGaji', fn ($q) => $q->where('status', 'dipublikasikan'))
            ->latest()
            ->get();

        return view('portal.slip-gaji', compact('slips'));
    }

    public function profil(Request $request)
    {
        $karyawan = Karyawan::with('cabang')->findOrFail($this->karyawanId($request));
        $rekapAbsensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', 'like', now()->format('Y-m') . '%')
            ->get()
            ->groupBy('status')
            ->map->count();

        return view('portal.profil', compact('karyawan', 'rekapAbsensi'));
    }
}
