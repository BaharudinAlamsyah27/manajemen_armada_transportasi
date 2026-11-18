<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BbmController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GantiPasswordController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\LaporanOperasionalController;
use App\Http\Controllers\PenagihanController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PetaArmadaController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\VerifikasiController;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Pelacakan publik (tanpa login): hanya status, kota, dan waktu — tanpa data pribadi atau koordinat GPS.
Route::get('/lacak/{kode_resi}', function (string $kode_resi) {
    $pesanan = Pesanan::withoutGlobalScopes()
        ->with('statusLogs')
        ->where('kode_resi', strtoupper($kode_resi))
        ->first();

    return view('pesanan.lacak-publik', compact('pesanan', 'kode_resi'));
})->middleware('throttle:30,1')->name('lacak.publik');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'aktif', 'ganti.password'])->group(function () {
    Route::get('/ganti-password', [GantiPasswordController::class, 'edit'])->name('password.ganti');
    Route::put('/ganti-password', [GantiPasswordController::class, 'update'])->name('password.ganti.simpan');
    Route::get('/berkas/bukti/{bukti}', [BerkasController::class, 'bukti'])->name('berkas.bukti');

    // ===== Panel admin =====
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('cabang', CabangController::class)->except(['show'])->middleware('role:admin_pusat');

        Route::resource('karyawan', KaryawanController::class)->except(['show']);
        Route::post('/karyawan/{karyawan}/akun', [KaryawanController::class, 'buatAkun'])->name('karyawan.akun');
        Route::post('/karyawan/{karyawan}/reset-password', [KaryawanController::class, 'resetPassword'])->name('karyawan.reset-password');

        Route::resource('kendaraan', KendaraanController::class)->except(['show']);

        Route::resource('pesanan', PesananController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status');

        Route::resource('pengiriman', PengirimanController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/pengiriman/{pengiriman}/mulai', [PengirimanController::class, 'mulai'])->name('pengiriman.mulai');
        Route::post('/pengiriman/{pengiriman}/selesai', [PengirimanController::class, 'selesai'])->name('pengiriman.selesai');
        Route::post('/pengiriman/{pengiriman}/batal', [PengirimanController::class, 'batal'])->name('pengiriman.batal');
        Route::post('/pengiriman/{pengiriman}/pesanan/{pesanan}/terkirim', [PengirimanController::class, 'terkirim'])->name('pengiriman.terkirim');
        Route::post('/pengiriman/{pengiriman}/pesanan/{pesanan}/gagal', [PengirimanController::class, 'gagal'])->name('pengiriman.gagal');

        Route::get('/bbm', [BbmController::class, 'index'])->name('bbm.index');
        Route::post('/bbm', [BbmController::class, 'store'])->name('bbm.store');

        Route::get('/servis', [ServisController::class, 'index'])->name('servis.index');
        Route::post('/servis', [ServisController::class, 'store'])->name('servis.store');
        Route::post('/servis/{servis}/selesai', [ServisController::class, 'selesai'])->name('servis.selesai');
        Route::post('/servis/stok', [ServisController::class, 'mutasiStok'])->name('servis.stok');

        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi/bbm/{bbmLog}', [VerifikasiController::class, 'bbm'])->name('verifikasi.bbm');
        Route::post('/verifikasi/biaya/{biaya}', [VerifikasiController::class, 'biaya'])->name('verifikasi.biaya');
        Route::get('/berkas/bbm/{bbmLog}', [BerkasController::class, 'bbm'])->name('berkas.bbm');
        Route::get('/berkas/biaya/{biaya}', [BerkasController::class, 'biaya'])->name('berkas.biaya');

        // Fase 9 — Pengumuman & Absensi
        Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
        Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
        Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');

        // Fase 11 — Penagihan (dapat diakses admin_cabang, admin_pusat, keuangan)
        Route::get('/penagihan', [PenagihanController::class, 'index'])->name('penagihan.index');
        Route::post('/penagihan/pelanggan', [PenagihanController::class, 'storePelanggan'])->name('penagihan.pelanggan');
        Route::post('/penagihan/tarif', [PenagihanController::class, 'storeTarif'])->name('penagihan.tarif');
        Route::post('/penagihan/invoice', [PenagihanController::class, 'storeInvoice'])->name('penagihan.invoice');
        Route::post('/penagihan/invoice/{invoice}/bayar', [PenagihanController::class, 'bayarInvoice'])->name('penagihan.bayar');
        Route::get('/penagihan/invoice/{invoice}/pdf', [PenagihanController::class, 'cetakInvoice'])->name('penagihan.invoice.pdf');

        // Fase 10, 12, 13, 14 — Keuangan & Penggajian (hanya admin_pusat & keuangan)
        Route::middleware('role:admin_pusat|keuangan')->group(function () {
            Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
            Route::post('/keuangan/jurnal', [KeuanganController::class, 'storeJurnal'])->name('keuangan.jurnal.store');
            Route::post('/keuangan/jurnal/{jurnal}/posting', [KeuanganController::class, 'posting'])->name('keuangan.jurnal.posting');
            Route::post('/keuangan/jurnal/{jurnal}/balik', [KeuanganController::class, 'balik'])->name('keuangan.jurnal.balik');
            Route::post('/keuangan/periode/{periode}/tutup', [KeuanganController::class, 'tutupPeriode'])->name('keuangan.periode.tutup');
            Route::get('/keuangan/pdf', [KeuanganController::class, 'pdf'])->name('keuangan.pdf');

            Route::get('/penggajian', [PenggajianController::class, 'index'])->name('penggajian.index');
            Route::post('/penggajian/hitung', [PenggajianController::class, 'hitung'])->name('penggajian.hitung');
            Route::post('/penggajian/{periode}/setujui', [PenggajianController::class, 'setujui'])->name('penggajian.setujui');
            Route::post('/penggajian/{periode}/bayar', [PenggajianController::class, 'bayar'])->name('penggajian.bayar');
        });

        // Fase 15, 16, 17 — Peta GPS, Laporan Operasional & Riwayat Perubahan
        Route::get('/peta-armada', [PetaArmadaController::class, 'index'])->name('peta.index');
        Route::get('/laporan/operasional', [LaporanOperasionalController::class, 'index'])->name('laporan.operasional');
        Route::get('/riwayat', [LaporanOperasionalController::class, 'riwayat'])->name('laporan.riwayat');
    });

    // ===== Portal sopir =====
    Route::middleware('sopir')->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalController::class, 'beranda'])->name('beranda');
        Route::get('/jadwal', [PortalController::class, 'jadwal'])->name('jadwal');
        Route::get('/pengiriman/{pengiriman}', [PortalController::class, 'pengiriman'])->whereNumber('pengiriman')->name('pengiriman');
        Route::post('/pengiriman/{pengiriman}/mulai', [PortalController::class, 'mulai'])->whereNumber('pengiriman')->name('mulai');
        Route::post('/pengiriman/{pengiriman}/selesai', [PortalController::class, 'selesai'])->whereNumber('pengiriman')->name('selesai');
        Route::post('/pengiriman/{pengiriman}/pesanan/{pesanan}/terkirim', [PortalController::class, 'terkirim'])->whereNumber(['pengiriman', 'pesanan'])->name('terkirim');
        Route::post('/pengiriman/{pengiriman}/pesanan/{pesanan}/gagal', [PortalController::class, 'gagal'])->whereNumber(['pengiriman', 'pesanan'])->name('gagal');
        Route::get('/bbm', [PortalController::class, 'catatBbm'])->name('bbm');
        Route::post('/bbm', [PortalController::class, 'simpanBbm'])->name('bbm.simpan');
        Route::get('/biaya', [PortalController::class, 'catatBiaya'])->name('biaya');
        Route::post('/biaya', [PortalController::class, 'simpanBiaya'])->name('biaya.simpan');
        Route::get('/pengumuman', [PortalController::class, 'pengumuman'])->name('pengumuman');
        Route::get('/slip-gaji', [PortalController::class, 'slipGaji'])->name('slip');
        Route::get('/profil', [PortalController::class, 'profil'])->name('profil');
        Route::post('/lokasi', [PetaArmadaController::class, 'simpanPing'])->middleware('throttle:6,1')->name('lokasi');
    });
});
