# Dokumentasi Teknis — FleetFlow ERP

## 1. Arsitektur & Teknologi
- **Backend:** PHP 8.2+, Laravel 12, Spatie Permission (`admin_pusat`, `keuangan`, `admin_cabang`, `sopir`), Spatie Activitylog, Barryvdh DomPDF.
- **Frontend:** Blade Templates, CSS Responsif (`public/css/app.css`), Leaflet + OpenStreetMap (Peta Armada).
- **Database:** SQLite (lokal default) atau MySQL (Laragon/Produksi).

## 2. Isolasi Multi-Cabang & Keamanan
- Trait [BelongsToCabang.php](file:///c:/laragon/www/fleetflow/app/Models/Concerns/BelongsToCabang.php) menerapkan *global scope* `cabang_id` pada seluruh model operasional & keuangan untuk peran selain `admin_pusat` dan `keuangan`.
- Middleware:
  - `aktif` ([PastikanPenggunaAktif.php](file:///c:/laragon/www/fleetflow/app/Http/Middleware/PastikanPenggunaAktif.php)): menolak pengguna atau karyawan nonaktif.
  - `ganti.password` ([WajibGantiPassword.php](file:///c:/laragon/www/fleetflow/app/Http/Middleware/WajibGantiPassword.php)): memaksa ganti sandi awal/reset.
  - `admin` ([HanyaAdmin.php](file:///c:/laragon/www/fleetflow/app/Http/Middleware/HanyaAdmin.php)) & `sopir` ([HanyaSopir.php](file:///c:/laragon/www/fleetflow/app/Http/Middleware/HanyaSopir.php)): memisahkan panel admin dan portal sopir.
- Kolom sensitif (`no_sim`, `no_rekening`, `no_hp_pengirim`, `no_hp_penerima`, `npwp`) dienkripsi menggunakan cast `encrypted`.
- Berkas unggahan (foto POD, struk BBM, biaya jalan) disimpan pada disk privat `local` (`storage/app/private`) dan diakses lewat [BerkasController.php](file:///c:/laragon/www/fleetflow/app/Http/Controllers/BerkasController.php).

## 3. Daftar Service Inti
- [NomorDokumenService.php](file:///c:/laragon/www/fleetflow/app/Services/NomorDokumenService.php): Penomoran dokumen berurutan dan aman konkurensi (`lockForUpdate`).
- [PesananService.php](file:///c:/laragon/www/fleetflow/app/Services/PesananService.php): Pembuatan resi acak `FF...` dan *state machine* status pesanan.
- [PengirimanService.php](file:///c:/laragon/www/fleetflow/app/Services/PengirimanService.php): Penjadwalan multi-rit (cek kapasitas, dokumen kendaraan, kesesuaian SIM, bentrok jadwal + jeda), eksekusi berangkat/terkirim/gagal/selesai/batal.
- [StokSukuCadangService.php](file:///c:/laragon/www/fleetflow/app/Services/StokSukuCadangService.php): Mutasi stok suku cadang anti-negatif.
- [JurnalService.php](file:///c:/laragon/www/fleetflow/app/Services/JurnalService.php) & [JurnalOtomatisService.php](file:///c:/laragon/www/fleetflow/app/Services/JurnalOtomatisService.php): Pembukuan berpasangan, validasi periode buka, maker-checker, jurnal balik, dan jurnal otomatis idempoten.
- [PenagihanService.php](file:///c:/laragon/www/fleetflow/app/Services/PenagihanService.php): Invoice dari pesanan terkirim & alokasi pembayaran pelanggan.
- [PayrollService.php](file:///c:/laragon/www/fleetflow/app/Services/PayrollService.php): Perhitungan gaji (kehadiran, ritase, kasbon), persetujuan maker-checker, dan publikasi slip.
- [LaporanKeuanganService.php](file:///c:/laragon/www/fleetflow/app/Services/LaporanKeuanganService.php): Neraca Saldo, Laba Rugi, Neraca, dan Rekonsiliasi subledger.
- [LaporanOperasionalService.php](file:///c:/laragon/www/fleetflow/app/Services/LaporanOperasionalService.php) & [LokasiService.php](file:///c:/laragon/www/fleetflow/app/Services/LokasiService.php): Biaya/kinerja armada per km & pelacakan GPS Haversine.

## 4. Menjalankan Secara Lokal & Pengujian
```powershell
php artisan migrate:fresh --seed
php vendor/bin/phpunit
```
