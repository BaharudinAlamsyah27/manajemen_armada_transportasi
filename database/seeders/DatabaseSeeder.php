<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\KomponenGaji;
use App\Models\Pelanggan;
use App\Models\Pemasok;
use App\Models\PeriodeAkuntansi;
use App\Models\Setting;
use App\Models\SukuCadang;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Peran (Roles)
        $rolePusat = Role::create(['name' => 'admin_pusat']);
        $roleKeuangan = Role::create(['name' => 'keuangan']);
        $roleCabang = Role::create(['name' => 'admin_cabang']);
        $roleSopir = Role::create(['name' => 'sopir']);

        // Cabang
        $cabang1 = Cabang::create([
            'kode' => 'JKT',
            'nama' => 'Cabang Jakarta',
            'kota' => 'Jakarta',
            'alamat' => 'Jl. Jend. Sudirman Kav. 45, Jakarta Selatan',
            'no_hp' => '021-5008891',
            'aktif' => true,
        ]);
        $cabang2 = Cabang::create([
            'kode' => 'BDG',
            'nama' => 'Cabang Bandung',
            'kota' => 'Bandung',
            'alamat' => 'Jl. Soekarno Hatta No. 210, Kota Bandung',
            'no_hp' => '022-7308892',
            'aktif' => true,
        ]);
        $cabang3 = Cabang::create([
            'kode' => 'SBY',
            'nama' => 'Cabang Surabaya',
            'kota' => 'Surabaya',
            'alamat' => 'Jl. Ahmad Yani No. 88, Kota Surabaya',
            'no_hp' => '031-8208893',
            'aktif' => true,
        ]);

        // Pengguna Pusat
        $userPusat = User::create([
            'name' => 'Admin Pusat',
            'username' => 'adminpusat',
            'email' => 'pusat@fleetflow.test',
            'password' => Hash::make('password'),
            'cabang_id' => null,
            'wajib_ganti_password' => false,
            'aktif' => true,
        ]);
        $userPusat->assignRole($rolePusat);

        $userKeuangan = User::create([
            'name' => 'Staf Keuangan',
            'username' => 'keuangan',
            'email' => 'keuangan@fleetflow.test',
            'password' => Hash::make('password'),
            'cabang_id' => null,
            'wajib_ganti_password' => false,
            'aktif' => true,
        ]);
        $userKeuangan->assignRole($roleKeuangan);

        // Admin Cabang
        foreach ([$cabang1, $cabang2, $cabang3] as $i => $cab) {
            $u = User::create([
                'name' => 'Admin ' . $cab->nama,
                'username' => 'admin' . strtolower($cab->kode),
                'password' => Hash::make('password'),
                'cabang_id' => $cab->id,
                'wajib_ganti_password' => false,
                'aktif' => true,
            ]);
            $u->assignRole($roleCabang);
            
            // Karyawan Sopir
            $karyawanSopir1 = Karyawan::create([
                'cabang_id' => $cab->id,
                'kode' => 'SPR-' . $cab->kode . '-01',
                'nama' => 'Sopir A ' . $cab->kode,
                'jabatan' => 'sopir',
                'jenis_sim' => 'B1',
                'sim_berlaku_sampai' => now()->addYears(2),
                'aktif' => true,
            ]);
            
            $karyawanSopir2 = Karyawan::create([
                'cabang_id' => $cab->id,
                'kode' => 'SPR-' . $cab->kode . '-02',
                'nama' => 'Sopir B ' . $cab->kode,
                'jabatan' => 'sopir',
                'jenis_sim' => 'B2',
                'sim_berlaku_sampai' => now()->addYears(1),
                'aktif' => true,
            ]);

            // Karyawan Non-Sopir
            Karyawan::create([
                'cabang_id' => $cab->id,
                'kode' => 'MK-' . $cab->kode . '-01',
                'nama' => 'Mekanik ' . $cab->kode,
                'jabatan' => 'mekanik',
                'aktif' => true,
            ]);

            // Akun Sopir (hanya 2 contoh di cabang 1 untuk uji coba)
            if ($cab->id === $cabang1->id) {
                $uSopir1 = User::create([
                    'name' => $karyawanSopir1->nama,
                    'username' => 'sopir1',
                    'password' => Hash::make('password'),
                    'cabang_id' => $cab->id,
                    'karyawan_id' => $karyawanSopir1->id,
                    'wajib_ganti_password' => true,
                    'aktif' => true,
                ]);
                $uSopir1->assignRole($roleSopir);

                $uSopir2 = User::create([
                    'name' => $karyawanSopir2->nama,
                    'username' => 'sopir2',
                    'password' => Hash::make('password'),
                    'cabang_id' => $cab->id,
                    'karyawan_id' => $karyawanSopir2->id,
                    'wajib_ganti_password' => true,
                    'aktif' => true,
                ]);
                $uSopir2->assignRole($roleSopir);
            }
            
            // Kendaraan
            Kendaraan::create([
                'cabang_id' => $cab->id,
                'kode' => 'KND-' . $cab->kode . '-01',
                'plat_nomor' => 'B 100' . $cab->id . ' AA',
                'jenis' => 'box',
                'kapasitas_kg' => 2000,
                'status' => 'tersedia',
                'stnk_berlaku_sampai' => now()->addYear(),
                'kir_berlaku_sampai' => now()->addMonths(6),
                'pajak_berlaku_sampai' => now()->addYear(),
            ]);
            
            Kendaraan::create([
                'cabang_id' => $cab->id,
                'kode' => 'KND-' . $cab->kode . '-02',
                'plat_nomor' => 'B 200' . $cab->id . ' BB',
                'jenis' => 'truk',
                'kapasitas_kg' => 5000,
                'status' => 'tersedia',
                'stnk_berlaku_sampai' => now()->addYear(),
                'kir_berlaku_sampai' => now()->addMonths(6),
                'pajak_berlaku_sampai' => now()->addYear(),
            ]);
        }

        // Suku Cadang Global
        SukuCadang::create(['kode' => 'OLI-01', 'nama' => 'Oli Mesin 10W-40', 'satuan' => 'Liter', 'harga_standar' => 60000, 'stok_minimum' => 10]);
        SukuCadang::create(['kode' => 'BAN-01', 'nama' => 'Ban Truk Engkel', 'satuan' => 'Pcs', 'harga_standar' => 1200000, 'stok_minimum' => 4]);

        // Settings
        Setting::create(['key' => 'maks_jam_kerja_sopir', 'value' => '12']);
        Setting::create(['key' => 'jeda_antar_rit_menit', 'value' => '30']);
        Setting::create(['key' => 'ppn_persen', 'value' => '0']);
        Setting::create(['key' => 'wajib_persetujuan_berbeda_user', 'value' => '1']);
        Setting::create(['key' => 'gps_interval_detik', 'value' => '30']);
        Setting::create(['key' => 'gps_akurasi_maks_m', 'value' => '100']);
        Setting::create(['key' => 'gps_retensi_hari', 'value' => '90']);

        // Bagan Akun Bawaan SAK EMKM (MODUL-KEUANGAN bagian 3)
        $baganAkun = [
            ['1-1100', 'Kas', 'aset', 'debit', null, true, true],
            ['1-1200', 'Bank', 'aset', 'debit', null, true, true],
            ['1-1300', 'Piutang Usaha', 'aset', 'debit', 'operasi', false, true],
            ['1-1400', 'Persediaan Suku Cadang', 'aset', 'debit', 'operasi', false, true],
            ['1-1500', 'Piutang Karyawan (Kasbon)', 'aset', 'debit', 'operasi', false, true],
            ['1-1650', 'Pajak Dibayar Dimuka', 'aset', 'debit', 'operasi', false, true],
            ['1-1700', 'PPN Masukan', 'aset', 'debit', 'operasi', false, true],
            ['1-2100', 'Kendaraan', 'aset', 'debit', 'investasi', false, true],
            ['1-2190', 'Akumulasi Penyusutan Kendaraan', 'aset', 'kredit', 'investasi', false, true],
            ['2-1100', 'Hutang Usaha', 'liabilitas', 'kredit', 'operasi', false, true],
            ['2-1200', 'Hutang Gaji', 'liabilitas', 'kredit', 'operasi', false, true],
            ['2-1300', 'PPN Keluaran', 'liabilitas', 'kredit', 'operasi', false, true],
            ['2-1400', 'Hutang Lain-lain', 'liabilitas', 'kredit', 'operasi', false, true],
            ['3-1100', 'Modal Disetor', 'ekuitas', 'kredit', 'pendanaan', false, true],
            ['3-2100', 'Laba Ditahan', 'ekuitas', 'kredit', null, false, true],
            ['4-1100', 'Pendapatan Jasa Angkut', 'pendapatan', 'kredit', 'operasi', false, true],
            ['4-1900', 'Pendapatan Lain-lain', 'pendapatan', 'kredit', 'operasi', false, true],
            ['5-1100', 'Beban BBM', 'beban', 'debit', 'operasi', false, true],
            ['5-1200', 'Beban Perawatan & Servis', 'beban', 'debit', 'operasi', false, true],
            ['5-1300', 'Beban Tol & Parkir', 'beban', 'debit', 'operasi', false, true],
            ['5-1400', 'Beban Denda & Lain Operasional', 'beban', 'debit', 'operasi', false, true],
            ['5-1500', 'Beban Pajak & Perizinan Kendaraan', 'beban', 'debit', 'operasi', false, true],
            ['5-1600', 'Beban Penyusutan Kendaraan', 'beban', 'debit', null, false, true],
            ['6-1100', 'Beban Gaji & Tunjangan', 'beban', 'debit', 'operasi', false, true],
            ['6-1200', 'Beban Uang Jalan/Ritase', 'beban', 'debit', 'operasi', false, true],
            ['6-2100', 'Beban Sewa & Utilitas', 'beban', 'debit', 'operasi', false, true],
            ['6-9900', 'Beban Lain-lain', 'beban', 'debit', 'operasi', false, true],
        ];
        foreach ($baganAkun as [$kode, $nama, $tipe, $normal, $arus, $isKas, $sistem]) {
            Akun::create([
                'kode' => $kode, 'nama' => $nama, 'tipe' => $tipe,
                'saldo_normal' => $normal, 'kategori_arus_kas' => $arus,
                'is_kas' => $isKas, 'aktif' => true, 'sistem' => $sistem,
            ]);
        }

        PeriodeAkuntansi::create(['tahun' => (int) now()->year, 'bulan' => (int) now()->month, 'status' => 'buka']);

        // Komponen Gaji Default
        $akunGaji = Akun::where('kode', '6-1100')->first();
        $akunRitase = Akun::where('kode', '6-1200')->first();
        KomponenGaji::create(['kode' => 'GAPOK', 'nama' => 'Gaji Pokok', 'jenis' => 'pendapatan', 'tipe_hitung' => 'tetap', 'nominal_default' => 3000000, 'akun_id' => $akunGaji?->id]);
        KomponenGaji::create(['kode' => 'MAKAN', 'nama' => 'Uang Makan Harian', 'jenis' => 'pendapatan', 'tipe_hitung' => 'per_hari_hadir', 'nominal_default' => 35000, 'akun_id' => $akunGaji?->id]);
        KomponenGaji::create(['kode' => 'RITASE', 'nama' => 'Uang Jalan / Ritase', 'jenis' => 'pendapatan', 'tipe_hitung' => 'per_ritase', 'nominal_default' => 100000, 'akun_id' => $akunRitase?->id]);

        // Master Pelanggan, Tarif & Pemasok Contoh
        $pelanggan1 = Pelanggan::create(['kode' => 'PLG-001', 'nama' => 'PT Nusantara Logistik', 'alamat' => 'Jl. Jend. Sudirman', 'kontak' => 'Budi', 'no_hp' => '08123456001', 'termin_hari' => 14]);
        Tarif::create(['kota_asal' => 'Jakarta', 'kota_tujuan' => 'Bandung', 'harga_per_kg' => 4500, 'tarif_minimum' => 50000, 'berlaku_sejak' => now()->toDateString()]);
        Tarif::create(['kota_asal' => 'Jakarta', 'kota_tujuan' => 'Surabaya', 'harga_per_kg' => 8500, 'tarif_minimum' => 100000, 'berlaku_sejak' => now()->toDateString()]);
        Pemasok::create(['kode' => 'PMS-001', 'nama' => 'Bengkel Mitra Armada', 'jenis' => 'bengkel', 'kontak' => '021-5551234', 'termin_hari' => 14]);

        // Data Transaksi Demo (hanya saat bukan menjalankan PHPUnit agar grafik & dashboard langsung terisi)
        if (!app()->runningUnitTests()) {
            $kndJkt = Kendaraan::withoutGlobalScopes()->where('cabang_id', $cabang1->id)->first();
            $sopirJkt = Karyawan::withoutGlobalScopes()->where('cabang_id', $cabang1->id)->where('jabatan', 'sopir')->first();

            if ($kndJkt && $sopirJkt) {
                \Illuminate\Support\Facades\Auth::login($userPusat);
                $pesananSvc = new \App\Services\PesananService();
                $pengirimanSvc = new \App\Services\PengirimanService();

                // 1. Pesanan & Pengiriman Selesai (2 hari lalu)
                $psSelesai = $pesananSvc->createPesanan([
                    'cabang_id' => $cabang1->id,
                    'pelanggan_id' => $pelanggan1->id,
                    'kode_resi' => 'FFDEMO000001',
                    'tanggal_pesan' => now()->subDays(2)->toDateString(),
                    'nama_pengirim' => 'PT Nusantara Logistik',
                    'no_hp_pengirim' => '08123456001',
                    'alamat_asal' => 'Kawasan Industri Pulogadung, Jakarta',
                    'kota_asal' => 'Jakarta',
                    'nama_penerima' => 'Gudang Distribusi Bandung',
                    'no_hp_penerima' => '081299998888',
                    'alamat_tujuan' => 'Jl. Soekarno Hatta No. 120, Bandung',
                    'kota_tujuan' => 'Bandung',
                    'deskripsi_barang' => ' Elektronik & Suku Cadang (20 Box)',
                    'berat_kg' => 250,
                    'jumlah_koli' => 20,
                    'tarif' => 1125000,
                ]);

                $m1 = $pengirimanSvc->createManifest([
                    'tanggal_rencana' => now()->subDays(2)->toDateString(),
                    'kendaraan_id' => $kndJkt->id,
                    'sopir_id' => $sopirJkt->id,
                    'waktu_mulai_rencana' => now()->subDays(2)->setTime(8, 0),
                    'estimasi_tiba' => now()->subDays(2)->setTime(13, 0),
                ], [$psSelesai->id]);
                $pengirimanSvc->mulaiPerjalanan($m1, 1000);
                $pengirimanSvc->tandaiTerkirim($psSelesai->fresh(), 'Hendra (Kepala Gudang)', null, 'Barang lengkap & aman', 'admin');
                $pengirimanSvc->selesaiPerjalanan($m1->fresh(), 1165);

                // Terbitkan Invoice otomatis untuk pesanan selesai
                (new \App\Services\PenagihanService())->buatInvoice($pelanggan1->id, [$psSelesai->id], 0, now()->subDay()->toDateString());

                // 2. Pesanan & Pengiriman Aktif Hari Ini
                $psAktif = $pesananSvc->createPesanan([
                    'cabang_id' => $cabang1->id,
                    'kode_resi' => 'FFDEMO000002',
                    'tanggal_pesan' => now()->toDateString(),
                    'nama_pengirim' => 'CV Sumber Makmur',
                    'no_hp_pengirim' => '081311112222',
                    'alamat_asal' => 'Jl. Daan Mogot KM 12, Jakarta Barat',
                    'kota_asal' => 'Jakarta',
                    'nama_penerima' => 'Toko Sentosa Jaya',
                    'no_hp_penerima' => '081377776666',
                    'alamat_tujuan' => 'Jl. Pasteur No. 45, Bandung',
                    'kota_tujuan' => 'Bandung',
                    'deskripsi_barang' => 'Bahan Tekstil (10 Roll)',
                    'berat_kg' => 180,
                    'jumlah_koli' => 10,
                    'tarif' => 810000,
                ]);
                $pengirimanSvc->createManifest([
                    'tanggal_rencana' => now()->toDateString(),
                    'kendaraan_id' => $kndJkt->id,
                    'sopir_id' => $sopirJkt->id,
                ], [$psAktif->id]);

                // 3. Pesanan Menunggu Jadwal
                $pesananSvc->createPesanan([
                    'cabang_id' => $cabang1->id,
                    'kode_resi' => 'FFDEMO000003',
                    'tanggal_pesan' => now()->toDateString(),
                    'nama_pengirim' => 'PT Maju Bersama',
                    'alamat_asal' => 'Tanjung Priok, Jakarta Utara',
                    'kota_asal' => 'Jakarta',
                    'nama_penerima' => 'PT Karya Mandiri',
                    'no_hp_penerima' => '081500009999',
                    'alamat_tujuan' => 'Kawasan Rungkut Industri, Surabaya',
                    'kota_tujuan' => 'Surabaya',
                    'deskripsi_barang' => 'Mesin Kompresor',
                    'berat_kg' => 300,
                    'jumlah_koli' => 3,
                    'tarif' => 2550000,
                ]);

                // 4. Catatan BBM, Servis & Biaya Operasional Bulan Ini (untuk Grafik Biaya)
                $bbm = \App\Models\BbmLog::create([
                    'cabang_id' => $cabang1->id,
                    'kendaraan_id' => $kndJkt->id,
                    'sopir_id' => $sopirJkt->id,
                    'tanggal' => now()->subDay()->toDateString(),
                    'liter' => 35,
                    'harga_per_liter' => 10000,
                    'total' => 350000,
                    'odometer' => 1180,
                    'tangki_penuh' => true,
                    'spbu' => 'SPBU Pertamina Rest Area KM 57',
                    'dicatat_oleh' => $userPusat->id,
                    'status_verifikasi' => 'terverifikasi',
                ]);
                (new \App\Services\JurnalOtomatisService())->catatBbmTerverifikasi($bbm);

                $biayaTol = \App\Models\BiayaOperasional::create([
                    'cabang_id' => $cabang1->id,
                    'kendaraan_id' => $kndJkt->id,
                    'pengiriman_id' => $m1->id,
                    'jenis' => 'tol',
                    'tanggal' => now()->subDays(2)->toDateString(),
                    'jumlah' => 145000,
                    'keterangan' => 'Tol Cipularang PP',
                    'dicatat_oleh' => $userPusat->id,
                    'status_verifikasi' => 'terverifikasi',
                ]);
                (new \App\Services\JurnalOtomatisService())->catatBiayaOperasional($biayaTol);

                $servis = \App\Models\ServisKendaraan::create([
                    'cabang_id' => $cabang1->id,
                    'kendaraan_id' => $kndJkt->id,
                    'jenis' => 'berkala',
                    'status' => 'selesai',
                    'tanggal_masuk' => now()->subDays(5)->toDateString(),
                    'tanggal_selesai' => now()->subDays(5)->toDateString(),
                    'odometer' => 980,
                    'bengkel' => 'Bengkel Mitra Armada',
                    'biaya_jasa' => 250000,
                    'total_biaya' => 250000,
                    'deskripsi' => 'Servis berkala & pengecekan rem',
                ]);
                (new \App\Services\JurnalOtomatisService())->catatServisSelesai($servis);

                \App\Models\AsetTetap::create([
                    'cabang_id' => $cabang1->id,
                    'kendaraan_id' => $kndJkt->id,
                    'nama' => 'Mitsubishi Colt Diesel Box ' . $kndJkt->plat_nomor,
                    'tanggal_perolehan' => now()->subMonths(12)->toDateString(),
                    'harga_perolehan' => 280000000,
                    'nilai_residu' => 40000000,
                    'umur_bulan' => 60,
                    'metode' => 'garis_lurus',
                    'akumulasi_penyusutan' => 4000000,
                    'status' => 'aktif',
                ]);

                \App\Models\Pengumuman::create([
                    'cabang_id' => null,
                    'judul' => 'SOP Pemeriksaan Kendaraan Sebelum Berangkat',
                    'isi' => 'Seluruh sopir wajib memeriksa tekanan ban, oli, air radiator, dan memastikan STNK/KIR aktif sebelum memulai perjalanan di aplikasi portal.',
                    'kategori' => 'keselamatan',
                    'target_peran' => ['sopir', 'admin_cabang'],
                    'disematkan' => true,
                    'terbit_pada' => now()->toDateString(),
                    'dibuat_oleh' => $userPusat->id,
                ]);

                \Illuminate\Support\Facades\Auth::logout();
            }
        }
    }
}
