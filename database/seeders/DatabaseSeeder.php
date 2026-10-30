<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Setting;
use App\Models\SukuCadang;
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
        $cabang1 = Cabang::create(['kode' => 'JKT', 'nama' => 'Cabang Jakarta', 'kota' => 'Jakarta', 'aktif' => true]);
        $cabang2 = Cabang::create(['kode' => 'BDG', 'nama' => 'Cabang Bandung', 'kota' => 'Bandung', 'aktif' => true]);
        $cabang3 = Cabang::create(['kode' => 'SBY', 'nama' => 'Cabang Surabaya', 'kota' => 'Surabaya', 'aktif' => true]);

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
    }
}
