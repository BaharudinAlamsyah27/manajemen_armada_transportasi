<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\AsetTetap;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kasbon;
use App\Models\Kendaraan;
use App\Models\Pemasok;
use App\Models\Penyusutan;
use App\Models\TagihanPemasok;
use App\Services\JurnalService;
use App\Services\NomorDokumenService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsetKasbonController extends Controller
{
    public function index()
    {
        $asets = AsetTetap::with(['cabang', 'kendaraan'])->latest()->get();
        $kasbons = Kasbon::with(['cabang', 'karyawan'])->latest()->get();
        $tagihans = TagihanPemasok::with(['cabang', 'pemasok'])->latest()->get();
        $cabangs = Cabang::where('aktif', true)->orderBy('nama')->get();
        $kendaraans = Kendaraan::orderBy('plat_nomor')->get();
        $karyawans = Karyawan::where('aktif', true)->orderBy('nama')->get();
        $pemasoks = Pemasok::where('aktif', true)->orderBy('nama')->get();

        return view('aset-kasbon.index', compact(
            'asets', 'kasbons', 'tagihans', 'cabangs', 'kendaraans', 'karyawans', 'pemasoks'
        ));
    }

    public function storeAset(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'kendaraan_id' => 'nullable|exists:kendaraans,id',
            'nama' => 'required|string|max:120',
            'tanggal_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:1',
            'nilai_residu' => 'required|numeric|min:0',
            'umur_bulan' => 'required|integer|min:1|max:240',
        ]);

        AsetTetap::create(array_merge($validated, [
            'metode' => 'garis_lurus',
            'akumulasi_penyusutan' => 0,
            'status' => 'aktif',
        ]));

        return back()->with('success', 'Aset tetap berhasil didaftarkan.');
    }

    public function jalankanPenyusutan()
    {
        try {
            $jurnalService = new JurnalService();
            $periode = $jurnalService->pastikanPeriodeTerbuka(now());
            $akunBeban = Akun::where('kode', '5-1600')->firstOrFail();
            $akunAkum = Akun::where('kode', '1-2190')->firstOrFail();

            $diproses = 0;
            DB::transaction(function () use ($periode, $jurnalService, $akunBeban, $akunAkum, &$diproses) {
                $asets = AsetTetap::withoutGlobalScopes()->where('status', 'aktif')->lockForUpdate()->get();
                foreach ($asets as $aset) {
                    if (Penyusutan::where('aset_id', $aset->id)->where('periode_id', $periode->id)->exists()) {
                        continue;
                    }
                    $sisaDisusutkan = (float) $aset->harga_perolehan - (float) $aset->nilai_residu - (float) $aset->akumulasi_penyusutan;
                    if ($sisaDisusutkan <= 0) {
                        continue;
                    }
                    $bulanan = round(((float) $aset->harga_perolehan - (float) $aset->nilai_residu) / max(1, (int) $aset->umur_bulan), 2);
                    $beban = min($bulanan, $sisaDisusutkan);

                    $jurnal = $jurnalService->buat([
                        'cabang_id' => $aset->cabang_id,
                        'tanggal' => now()->toDateString(),
                        'keterangan' => "Penyusutan Bulanan {$aset->nama} ({$periode->bulan}/{$periode->tahun})",
                        'jenis' => 'otomatis',
                        'peristiwa' => "penyusutan_{$periode->id}",
                        'sumber_type' => AsetTetap::class,
                        'sumber_id' => $aset->id,
                    ], [
                        ['akun_id' => $akunBeban->id, 'debit' => $beban, 'kredit' => 0, 'kendaraan_id' => $aset->kendaraan_id],
                        ['akun_id' => $akunAkum->id, 'debit' => 0, 'kredit' => $beban, 'kendaraan_id' => $aset->kendaraan_id],
                    ], true);

                    Penyusutan::create([
                        'aset_id' => $aset->id,
                        'periode_id' => $periode->id,
                        'jumlah' => $beban,
                        'jurnal_id' => $jurnal->id,
                    ]);

                    $aset->update([
                        'akumulasi_penyusutan' => round((float) $aset->akumulasi_penyusutan + $beban, 2),
                    ]);
                    $diproses++;
                }
            });

            return back()->with('success', "Penyusutan bulanan selesai diproses untuk {$diproses} aset aktif.");
        } catch (Exception $e) {
            return back()->withErrors(['penyusutan' => $e->getMessage()]);
        }
    }

    public function storeKasbon(Request $request)
    {
        $validated = $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:10000',
            'cicilan_per_bulan' => 'required|numeric|min:1000',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $karyawan = Karyawan::withoutGlobalScopes()->findOrFail($validated['karyawan_id']);
                $akunPiutangKaryawan = Akun::where('kode', '1-1500')->firstOrFail();
                $akunKas = Akun::where('kode', '1-1100')->firstOrFail();

                $jurnal = (new JurnalService())->buat([
                    'cabang_id' => $karyawan->cabang_id,
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => "Pencairan Kasbon {$karyawan->nama}",
                    'jenis' => 'otomatis',
                ], [
                    ['akun_id' => $akunPiutangKaryawan->id, 'debit' => (float) $validated['jumlah'], 'kredit' => 0],
                    ['akun_id' => $akunKas->id, 'debit' => 0, 'kredit' => (float) $validated['jumlah']],
                ], true);

                Kasbon::create([
                    'cabang_id' => $karyawan->cabang_id,
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $validated['tanggal'],
                    'jumlah' => $validated['jumlah'],
                    'cicilan_per_bulan' => $validated['cicilan_per_bulan'],
                    'sisa' => $validated['jumlah'],
                    'status' => 'aktif',
                    'jurnal_id' => $jurnal->id,
                ]);
            });

            return back()->with('success', 'Kasbon karyawan berhasil dicatat & dijurnal.');
        } catch (Exception $e) {
            return back()->withErrors(['kasbon' => $e->getMessage()]);
        }
    }

    public function storeTagihanPemasok(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'pemasok_id' => 'required|exists:pemasoks,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $pemasok = Pemasok::findOrFail($validated['pemasok_id']);
                $akunBeban = Akun::where('kode', '5-1200')->firstOrFail();
                $akunHutang = Akun::where('kode', '2-1100')->firstOrFail();

                $noTagihan = NomorDokumenService::buat((int) $validated['cabang_id'], 'TGH', date('Ym'));

                $tagihan = TagihanPemasok::create([
                    'cabang_id' => $validated['cabang_id'],
                    'no_tagihan' => $noTagihan,
                    'pemasok_id' => $pemasok->id,
                    'tanggal' => $validated['tanggal'],
                    'jatuh_tempo' => now()->parse($validated['tanggal'])->addDays($pemasok->termin_hari)->toDateString(),
                    'jumlah' => $validated['jumlah'],
                    'terbayar' => 0,
                    'status' => 'terbit',
                ]);

                (new JurnalService())->buat([
                    'cabang_id' => $validated['cabang_id'],
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => "Tagihan Pemasok {$noTagihan} ({$pemasok->nama})",
                    'jenis' => 'otomatis',
                    'peristiwa' => 'tagihan_pemasok',
                    'sumber_type' => TagihanPemasok::class,
                    'sumber_id' => $tagihan->id,
                ], [
                    ['akun_id' => $akunBeban->id, 'debit' => (float) $validated['jumlah'], 'kredit' => 0],
                    ['akun_id' => $akunHutang->id, 'debit' => 0, 'kredit' => (float) $validated['jumlah']],
                ], true);
            });

            return back()->with('success', 'Tagihan pemasok (Hutang Usaha) berhasil dicatat.');
        } catch (Exception $e) {
            return back()->withErrors(['tagihan' => $e->getMessage()]);
        }
    }
}
