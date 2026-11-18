<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BbmController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ServisController;
use App\Models\Pesanan;

Route::get('/', function () {
    return redirect()->route('login');
});

// Pelacakan Publik (Tanpa Login)
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

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('cabang', CabangController::class)->except(['show']);
    Route::resource('karyawan', KaryawanController::class)->except(['show']);
    Route::resource('kendaraan', KendaraanController::class)->except(['show']);

    Route::resource('pesanan', PesananController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.status');

    Route::resource('pengiriman', PengirimanController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/pengiriman/{pengiriman}/mulai', [PengirimanController::class, 'mulai'])->name('pengiriman.mulai');
    Route::post('/pengiriman/{pengiriman}/selesai', [PengirimanController::class, 'selesai'])->name('pengiriman.selesai');

    Route::get('/bbm', [BbmController::class, 'index'])->name('bbm.index');
    Route::post('/bbm', [BbmController::class, 'store'])->name('bbm.store');

    Route::get('/servis', [ServisController::class, 'index'])->name('servis.index');
    Route::post('/servis', [ServisController::class, 'store'])->name('servis.store');
    Route::post('/servis/{servis}/selesai', [ServisController::class, 'selesai'])->name('servis.selesai');
    Route::post('/servis/stok', [ServisController::class, 'mutasiStok'])->name('servis.stok');
});
