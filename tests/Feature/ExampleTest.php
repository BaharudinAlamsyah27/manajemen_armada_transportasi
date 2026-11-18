<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\SukuCadang;
use App\Models\User;
use App\Services\PengirimanService;
use App\Services\PesananService;
use App\Services\StokSukuCadangService;
use Database\Seeders\DatabaseSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_and_root_redirect_work(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertStatus(200);
    }

    public function test_branch_isolation_and_dashboard_work(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $this->actingAs($adminJkt);

        // Admin Jakarta hanya melihat kendaraan & karyawan Cabang Jakarta
        $this->assertEquals(2, Kendaraan::count());
        $this->assertEquals(3, Karyawan::count());

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/kendaraan')->assertStatus(200);
        $this->get('/karyawan')->assertStatus(200);
    }

    public function test_pesanan_and_pengiriman_workflow_and_overload_protection(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $this->actingAs($adminJkt);

        $pesananService = new PesananService();
        $pesanan = $pesananService->createPesanan([
            'nama_pengirim' => 'PT Maju Jaya',
            'no_hp_pengirim' => '0811111111',
            'alamat_asal' => 'Jl. Sudirman No. 1',
            'kota_asal' => 'Jakarta',
            'nama_penerima' => 'Toko Berkah',
            'no_hp_penerima' => '0822222222',
            'alamat_tujuan' => 'Jl. Asia Afrika No. 10',
            'kota_tujuan' => 'Bandung',
            'berat_kg' => 500,
            'jumlah_koli' => 10,
        ]);

        $this->assertEquals('menunggu_jadwal', $pesanan->status);
        $this->assertStringStartsWith('FF', $pesanan->kode_resi);

        $kendaraan = Kendaraan::where('jenis', 'box')->firstOrFail(); // kapasitas 2000 kg
        $sopir = Karyawan::where('jabatan', 'sopir')->firstOrFail();

        $pengirimanService = new PengirimanService();
        $manifest = $pengirimanService->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $kendaraan->id,
            'sopir_id' => $sopir->id,
        ], [$pesanan->id]);

        $this->assertEquals('terjadwal', $manifest->status);
        $this->assertEquals('terjadwal', $pesanan->fresh()->status);

        // Mulai perjalanan
        $pengirimanService->mulaiPerjalanan($manifest, 100);
        $this->assertEquals('berjalan', $manifest->fresh()->status);
        $this->assertEquals('bertugas', $kendaraan->fresh()->status);
        $this->assertEquals('dalam_perjalanan', $pesanan->fresh()->status);

        // Tandai pesanan terkirim (input manual admin dengan catatan)
        $pengirimanService->tandaiTerkirim($pesanan->fresh(), 'Budi', null, 'Diterima di gudang', 'admin');
        $this->assertEquals('terkirim', $pesanan->fresh()->status);

        // Selesai perjalanan
        $pengirimanService->selesaiPerjalanan($manifest->fresh(), 250);
        $this->assertEquals('selesai', $manifest->fresh()->status);
        $this->assertEquals('tersedia', $kendaraan->fresh()->status);
        $this->assertEquals(250, $kendaraan->fresh()->odometer_terakhir);
        $this->assertEquals('terkirim', $pesanan->fresh()->status);
    }

    public function test_stok_suku_cadang_cannot_go_negative(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cabang = Cabang::firstOrFail();
        $oli = SukuCadang::where('kode', 'OLI-01')->firstOrFail();

        $stokService = new StokSukuCadangService();
        $stok = $stokService->mutasi($cabang->id, $oli->id, 'masuk', 5, 60000, 'Stok awal');
        $this->assertEquals(5, $stok->qty);

        $stok = $stokService->mutasi($cabang->id, $oli->id, 'keluar_servis', 3, 60000, 'Pemakaian servis');
        $this->assertEquals(2, $stok->qty);

        $this->expectException(Exception::class);
        $stokService->mutasi($cabang->id, $oli->id, 'keluar_servis', 5, 60000, 'Melebihi stok');
    }
}
