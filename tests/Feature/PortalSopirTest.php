<?php

namespace Tests\Feature;

use App\Models\BbmLog;
use App\Models\BuktiPengiriman;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pengiriman;
use App\Models\User;
use App\Services\PengirimanService;
use App\Services\PesananService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalSopirTest extends TestCase
{
    use RefreshDatabase;

    public function test_sopir_wajib_ganti_password_dan_tidak_bisa_akses_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $sopir1 = User::where('username', 'sopir1')->firstOrFail();
        $this->actingAs($sopir1);

        // Wajib ganti password saat login pertama
        $this->get('/portal')->assertRedirect('/ganti-password');

        // Setelah password diganti, diarahkan ke portal
        $this->put('/ganti-password', [
            'password_lama' => 'password',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertRedirect('/portal');

        $this->assertFalse($sopir1->fresh()->wajib_ganti_password);

        // Sopir tidak boleh akses rute admin
        $this->get('/dashboard')->assertRedirect('/portal');
        $this->getJson('/dashboard')->assertStatus(403);
        $this->get('/kendaraan')->assertRedirect('/portal');
    }

    public function test_sopir_hanya_melihat_pengiriman_miliknya_dan_aksi_lapangan_dengan_foto_privat(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $sopir1User = User::where('username', 'sopir1')->firstOrFail();
        $sopir1User->update(['wajib_ganti_password' => false]);

        $sopir2User = User::where('username', 'sopir2')->firstOrFail();
        $sopir2User->update(['wajib_ganti_password' => false]);

        $this->actingAs($adminJkt);

        $pesanan = (new PesananService())->createPesanan([
            'nama_pengirim' => 'PT A',
            'no_hp_pengirim' => '081100001111',
            'alamat_asal' => 'Jl. A',
            'kota_asal' => 'Jakarta',
            'nama_penerima' => 'Toko B',
            'no_hp_penerima' => '081234567890',
            'alamat_tujuan' => 'Jl. B',
            'kota_tujuan' => 'Bekasi',
            'berat_kg' => 50,
            'jumlah_koli' => 1,
        ]);

        $kendaraan = Kendaraan::where('jenis', 'box')->firstOrFail();

        $manifest = (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $kendaraan->id,
            'sopir_id' => $sopir1User->karyawan_id,
        ], [$pesanan->id]);

        // Sopir 2 tidak boleh melihat manifest milik Sopir 1
        $this->actingAs($sopir2User);
        $this->get("/portal/pengiriman/{$manifest->id}")->assertStatus(404);

        // Sopir 1 melihat manifest dan nomor telepon penerima penuh karena pengiriman aktif hari ini
        $this->actingAs($sopir1User);
        $this->get("/portal/pengiriman/{$manifest->id}")
            ->assertStatus(200)
            ->assertSee('081234567890');

        // Mulai perjalanan
        $this->post("/portal/pengiriman/{$manifest->id}/mulai", [
            'odometer_awal' => 100,
        ])->assertRedirect("/portal/pengiriman/{$manifest->id}");

        // Upload non-gambar ditolak
        $this->post("/portal/pengiriman/{$manifest->id}/pesanan/{$pesanan->id}/terkirim", [
            'nama_penerima' => 'Budi',
            'foto' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('foto');

        // Upload foto valid -> tersimpan di disk privat 'local'
        $this->post("/portal/pengiriman/{$manifest->id}/pesanan/{$pesanan->id}/terkirim", [
            'nama_penerima' => 'Budi',
            'foto' => UploadedFile::fake()->image('bukti.jpg', 400, 400),
            'catatan' => 'Titip satpam',
        ])->assertRedirect("/portal/pengiriman/{$manifest->id}");

        $bukti = BuktiPengiriman::firstOrFail();
        Storage::disk('local')->assertExists($bukti->path);

        // Selesai pengiriman
        $this->post("/portal/pengiriman/{$manifest->id}/selesai", [
            'odometer_akhir' => 145,
        ])->assertRedirect("/portal/pengiriman/{$manifest->id}");

        $this->assertEquals('selesai', $manifest->fresh()->status);

        // Setelah selesai, nomor telepon penerima disamarkan kembali di portal sopir
        $this->get("/portal/pengiriman/{$manifest->id}")
            ->assertStatus(200)
            ->assertDontSee('081234567890')
            ->assertSee('••••••7890');
    }

    public function test_input_bbm_sopir_menunggu_verifikasi_lalu_disetujui_admin(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $sopir1User = User::where('username', 'sopir1')->firstOrFail();
        $sopir1User->update(['wajib_ganti_password' => false]);

        $this->actingAs($adminJkt);
        $pesanan = (new PesananService())->createPesanan([
            'nama_pengirim' => 'PT A',
            'alamat_asal' => 'Jl. A',
            'kota_asal' => 'Jakarta',
            'nama_penerima' => 'Toko B',
            'no_hp_penerima' => '081234567890',
            'alamat_tujuan' => 'Jl. B',
            'kota_tujuan' => 'Bekasi',
            'berat_kg' => 50,
            'jumlah_koli' => 1,
        ]);
        $kendaraan = Kendaraan::where('jenis', 'box')->firstOrFail();
        (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $kendaraan->id,
            'sopir_id' => $sopir1User->karyawan_id,
        ], [$pesanan->id]);

        // Sopir catat BBM
        $this->actingAs($sopir1User);
        $this->post('/portal/bbm', [
            'kendaraan_id' => $kendaraan->id,
            'tanggal' => now()->toDateString(),
            'liter' => 20,
            'harga_per_liter' => 10000,
            'odometer' => 120,
            'tangki_penuh' => '1',
            'foto' => UploadedFile::fake()->image('struk.jpg'),
        ])->assertRedirect('/portal');

        $log = BbmLog::withoutGlobalScopes()->firstOrFail();
        $this->assertEquals('menunggu_verifikasi', $log->status_verifikasi);
        $this->assertEquals(200000, (float) $log->total);

        // Admin cabang verifikasi
        $this->actingAs($adminJkt);
        $this->get('/verifikasi')->assertStatus(200);
        $this->post("/verifikasi/bbm/{$log->id}", [
            'keputusan' => 'setujui',
        ])->assertSessionHasNoErrors();

        $this->assertEquals('terverifikasi', $log->fresh()->status_verifikasi);
        $this->assertEquals(120, $kendaraan->fresh()->odometer_terakhir);
    }
}
