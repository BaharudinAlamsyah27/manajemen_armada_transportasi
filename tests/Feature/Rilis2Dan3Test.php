<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Akun;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Pengiriman;
use App\Models\Pengumuman;
use App\Models\PeriodeAkuntansi;
use App\Models\User;
use App\Services\JurnalService;
use App\Services\LaporanKeuanganService;
use App\Services\LokasiService;
use App\Services\PayrollService;
use App\Services\PenagihanService;
use App\Services\PengirimanService;
use App\Services\PesananService;
use Database\Seeders\DatabaseSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Rilis2Dan3Test extends TestCase
{
    use RefreshDatabase;

    public function test_pengumuman_dan_absensi_fase_9(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $adminBdg = User::where('username', 'adminbdg')->firstOrFail();
        $sopir1 = User::where('username', 'sopir1')->firstOrFail();
        $sopir1->update(['wajib_ganti_password' => false]);

        $this->actingAs($adminJkt);
        $this->post('/pengumuman', [
            'judul' => 'Info Keselamatan JKT',
            'isi' => 'Cek rem sebelum berangkat',
            'kategori' => 'keselamatan',
            'terbit_pada' => now()->toDateString(),
        ])->assertRedirect('/pengumuman');

        $p = Pengumuman::firstOrFail();

        // Admin Bandung tidak boleh menghapus pengumuman Jakarta
        $this->actingAs($adminBdg);
        $this->delete("/pengumuman/{$p->id}")->assertStatus(403);

        // Sopir Jakarta melihat pengumuman & otomatis tercatat dibaca
        $this->actingAs($sopir1);
        $this->get('/portal/pengumuman')->assertStatus(200)->assertSee('Info Keselamatan JKT');
        $this->assertDatabaseHas('pengumuman_dibacas', [
            'pengumuman_id' => $p->id,
            'user_id' => $sopir1->id,
        ]);

        // Absensi harian
        $this->actingAs($adminJkt);
        $this->post('/absensi', [
            'tanggal' => now()->toDateString(),
            'status' => [$sopir1->karyawan_id => 'hadir'],
        ])->assertRedirect('/absensi?tanggal=' . now()->toDateString());

        $this->assertEquals(1, Absensi::where('karyawan_id', $sopir1->karyawan_id)->where('status', 'hadir')->count());
    }

    public function test_kesesuaian_sim_dan_bentrok_multirit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $this->actingAs($adminJkt);

        $truk = Kendaraan::where('jenis', 'truk')->firstOrFail();
        $sopirB1 = Karyawan::where('jabatan', 'sopir')->where('jenis_sim', 'B1')->firstOrFail();
        $sopirB2 = Karyawan::where('jabatan', 'sopir')->where('jenis_sim', 'B2')->firstOrFail();

        $ps1 = (new PesananService())->createPesanan([
            'nama_penerima' => 'P1', 'alamat_tujuan' => 'A1', 'kota_tujuan' => 'Bandung', 'berat_kg' => 100,
        ]);
        $ps2 = (new PesananService())->createPesanan([
            'nama_penerima' => 'P2', 'alamat_tujuan' => 'A2', 'kota_tujuan' => 'Bandung', 'berat_kg' => 100,
        ]);

        // Sopir B1 bawa truk harus ditolak (truk butuh B2)
        try {
            (new PengirimanService())->createManifest([
                'tanggal_rencana' => now()->toDateString(),
                'kendaraan_id' => $truk->id,
                'sopir_id' => $sopirB1->id,
            ], [$ps1->id]);
            $this->fail('Seharusnya gagal karena SIM B1 tidak cocok untuk truk.');
        } catch (Exception $e) {
            $this->assertStringContainsString('SIM', $e->getMessage());
        }

        // Jadwal 1 dengan sopir B2 berhasil
        $mulai = now()->setTime(8, 0);
        (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $truk->id,
            'sopir_id' => $sopirB2->id,
            'waktu_mulai_rencana' => $mulai,
            'estimasi_tiba' => $mulai->copy()->addHours(2),
        ], [$ps1->id]);

        // Jadwal 2 bentrok dengan jeda 30 menit -> ditolak
        $this->expectException(Exception::class);
        (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $truk->id,
            'sopir_id' => $sopirB2->id,
            'waktu_mulai_rencana' => $mulai->copy()->addHours(2)->addMinutes(10),
            'estimasi_tiba' => $mulai->copy()->addHours(4),
        ], [$ps2->id]);
    }

    public function test_akuntansi_penagihan_penggajian_dan_otorisasi_keuangan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminCabang = User::where('username', 'adminjkt')->firstOrFail();
        $keuangan = User::where('username', 'keuangan')->firstOrFail();
        $adminPusat = User::where('username', 'adminpusat')->firstOrFail();
        $sopir1User = User::where('username', 'sopir1')->firstOrFail();
        $sopir1User->update(['wajib_ganti_password' => false]);

        // admin_cabang ditolak (403) di rute keuangan & penggajian
        $this->actingAs($adminCabang);
        $this->get('/keuangan')->assertStatus(403);
        $this->get('/penggajian')->assertStatus(403);

        // Buat pesanan lalu selesaikan pengiriman agar bisa ditagih
        $ps = (new PesananService())->createPesanan([
            'nama_penerima' => 'PT Pelanggan', 'alamat_tujuan' => 'Jl. Merdeka', 'kota_tujuan' => 'Bandung',
            'berat_kg' => 100, 'tarif' => 500000,
        ]);
        $box = Kendaraan::where('jenis', 'box')->firstOrFail();
        $manifest = (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $box->id,
            'sopir_id' => $sopir1User->karyawan_id,
        ], [$ps->id]);
        (new PengirimanService())->mulaiPerjalanan($manifest, 100);
        (new PengirimanService())->tandaiTerkirim($ps->fresh(), 'Budi', null, 'OK', 'admin');
        (new PengirimanService())->selesaiPerjalanan($manifest->fresh(), 150);

        // Penagihan (Invoice + Pembayaran)
        $this->actingAs($keuangan);
        $pelanggan = Pelanggan::firstOrFail();
        $invoice = (new PenagihanService())->buatInvoice($pelanggan->id, [$ps->id], 0);
        $this->assertEquals(500000, (float) $invoice->total);

        $rekonsiliasi = (new LaporanKeuanganService())->rekonsiliasi();
        $this->assertEquals(500000, $rekonsiliasi['piutang_subledger']);
        $this->assertEquals(500000, $rekonsiliasi['piutang_akun']);
        $this->assertTrue($rekonsiliasi['neraca_seimbang']);

        $akunKas = Akun::where('kode', '1-1100')->firstOrFail();
        (new PenagihanService())->catatPembayaran($pelanggan->id, $akunKas->id, [$invoice->id => 500000], now()->toDateString());
        $this->assertEquals('lunas', $invoice->fresh()->status);

        // Jurnal tidak seimbang ditolak
        try {
            (new JurnalService())->buat(['tanggal' => now()->toDateString(), 'keterangan' => 'Salah'], [
                ['akun_id' => $akunKas->id, 'debit' => 1000, 'kredit' => 0],
                ['akun_id' => Akun::where('kode', '4-1100')->firstOrFail()->id, 'debit' => 0, 'kredit' => 500],
            ], true);
            $this->fail('Jurnal tidak seimbang seharusnya ditolak.');
        } catch (Exception $e) {
            $this->assertStringContainsString('tidak seimbang', $e->getMessage());
        }

        // Penggajian + Maker-Checker + Portal Sopir
        $cabangJkt = Cabang::where('kode', 'JKT')->firstOrFail();
        $periodeGaji = (new PayrollService())->hitungPeriode($cabangJkt->id, (int) now()->year, (int) now()->month);
        $this->assertEquals('dihitung', $periodeGaji->status);

        // Maker-checker: pembuat (keuangan) tidak boleh menyetujui sendiri; adminPusat yang setujui
        $this->actingAs($adminPusat);
        (new PayrollService())->setujui($periodeGaji->fresh());
        (new PayrollService())->bayarDanPublikasikan($periodeGaji->fresh(), $akunKas->id, now()->toDateString());

        $this->actingAs($sopir1User);
        $this->get('/portal/slip-gaji')->assertStatus(200)->assertSee('SLP-JKT');
    }

    public function test_gps_haversine_dan_ping_lokasi(): void
    {
        $this->seed(DatabaseSeeder::class);
        $adminJkt = User::where('username', 'adminjkt')->firstOrFail();
        $sopir1User = User::where('username', 'sopir1')->firstOrFail();
        $sopir1User->update(['wajib_ganti_password' => false]);

        $this->actingAs($adminJkt);
        $ps = (new PesananService())->createPesanan([
            'nama_penerima' => 'PT X', 'alamat_tujuan' => 'Jl. X', 'kota_tujuan' => 'Bogor', 'berat_kg' => 50,
        ]);
        $box = Kendaraan::where('jenis', 'box')->firstOrFail();
        $manifest = (new PengirimanService())->createManifest([
            'tanggal_rencana' => now()->toDateString(),
            'kendaraan_id' => $box->id,
            'sopir_id' => $sopir1User->karyawan_id,
        ], [$ps->id]);
        (new PengirimanService())->mulaiPerjalanan($manifest, 100);

        $this->actingAs($sopir1User);
        $this->postJson('/portal/lokasi', [
            'pengiriman_id' => $manifest->id,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'akurasi_m' => 15,
        ])->assertStatus(200)->assertJson(['ok' => true, 'valid' => true]);

        // Cek jarak Haversine Jakarta -> Bandung (~115-125 km garis lurus)
        $jarak = LokasiService::haversine(-6.200000, 106.816666, -6.917464, 107.619123);
        $this->assertGreaterThan(100, $jarak);
        $this->assertLessThan(140, $jarak);
    }
}
