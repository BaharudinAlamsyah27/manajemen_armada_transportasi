# FleetFlow ERP — Sistem Manajemen Armada, Logistik & Akuntansi Multi-Cabang

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Tests](https://img.shields.io/badge/PHPUnit-12%20Passing%20(84%20Assertions)-16A34A?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)

**FleetFlow ERP** adalah aplikasi web *full-stack* manajemen armada, ekspedisi kargo, dan akuntansi multi-cabang yang dirancang untuk perusahaan logistik. Proyek ini menggabungkan **Halaman Layanan Publik (Company Profile + Lacak Resi + Cek Ongkir)**, **Dashboard ERP Operasional & Keuangan**, serta **Portal Sopir Mobile-First dengan Pelacakan GPS**.

---

## Fitur Utama

### 1. Layanan Publik (`/`)
- **Company Profile Modern:** Profil perusahaan, 6 keunggulan layanan, katalog kapasitas armada (Motor, Pickup, Truk Box, Truk Fuso), serta alamat jaringan cabang dinamis dari database.
- **Lacak Resi Real-Time:** Pelanggan dapat melacak status & *timeline* pengiriman tanpa perlu login (data pribadi pengirim/penerima tetap terlindungi).
- **Kalkulator Estimasi Ongkir:** Menghitung estimasi biaya kirim secara instan berdasarkan kota asal, tujuan, dan berat muatan (kg).

### 2. Operasional & Manajemen Armada Multi-Cabang
- **Isolasi Data Multi-Cabang (`BelongsToCabang`):** Admin cabang hanya dapat melihat dan mengelola data cabangnya; Admin Pusat & Staf Keuangan memiliki akses konsolidasi semua cabang.
- **Pesanan & Resi (`FF...`):** Generator kode resi acak, *state machine* transisi status yang ketat, enkripsi nomor HP (`encrypted`), dan **Cetak Label Resi PDF**.
- **Penjadwalan Manifest Multi-Rit:** Validasi otomatis anti-*overload*, masa berlaku STNK/KIR/Pajak/SIM, kesesuaian golongan SIM terhadap jenis kendaraan, serta pencegahan bentrok jadwal + jeda antar rit. Dilengkapi **Cetak Surat Jalan PDF**.
- **BBM, Servis & Stok Suku Cadang:** Pencatatan BBM (total dihitung di server + validasi odometer), servis armada (otomatis mengubah status kendaraan), serta mutasi stok suku cadang anti-negatif.

### 3. Portal Sopir (Mobile-First) & Pelacakan GPS
- **Akses Terisolasi:** Sopir memiliki antarmuka khusus ponsel (`/portal`) dan diblokir dari rute admin. Wajib ganti kata sandi awal pada login pertama.
- **Eksekusi Lapangan & e-POD:** Mulai perjalanan, tandai pesanan terkirim dengan unggah foto bukti (*Proof of Delivery*) ke disk privat (`storage/app/private`), catat BBM & biaya jalan (masuk antrean verifikasi admin cabang).
- **Peta Armada (Leaflet + OpenStreetMap):** Pelacakan lokasi GPS aktif selama perjalanan berjalan beserta perhitungan jarak Haversine.
- **Pengumuman, Absensi & Slip Gaji:** Sopir dapat membaca pengumuman terarah, melihat rekap kehadiran bulanan, dan melihat slip gaji yang dipublikasikan.

### 4. Akuntansi Berpasangan (SAK EMKM) & Penggajian
- **Pembukuan Berpasangan (`JurnalService`):** Validasi keseimbangan Debit = Kredit, *maker-checker* untuk jurnal manual & penggajian, jurnal balik, dan penguncian periode akuntansi.
- **Jurnal Otomatis Idempoten:** Terbentuk otomatis saat penerbitan invoice, pembayaran pelanggan, verifikasi BBM/biaya jalan, servis selesai, penyusutan aset tetap, kasbon, dan penggajian.
- **Laporan Keuangan & Operasional:** Neraca Saldo, Laba Rugi, Neraca seimbang, Rekonsiliasi subledger, **Unduh Paket Laporan PDF**, serta **Laporan Biaya & Performa Armada (Ekspor CSV)**.

---

## Akun Demo (Seeder Bawaan)

Seluruh akun demo menggunakan password: **`password`** (tersedia tombol **Demo Quick Login 1-Klik** di halaman `/login`):

| Peran | Username | Email | Cakupan Akses |
|---|---|---|---|
| **Admin Pusat** | `adminpusat` | `pusat@fleetflow.test` | Semua cabang, master, operasional, keuangan, pengaturan |
| **Staf Keuangan** | `keuangan` | `keuangan@fleetflow.test` | Akuntansi, jurnal, penagihan, aset, kasbon, penggajian, laporan |
| **Admin Cabang** | `adminjkt` / `adminbdg` / `adminsby` | – | Operasional, verifikasi lapangan, & penagihan khusus cabangnya |
| **Sopir** | `sopir1` / `sopir2` | – | Portal Sopir mobile-first (jadwal, e-POD, BBM, slip gaji) |

Resi demo yang bisa langsung dicoba di halaman Lacak Resi: **`FFDEMO000001`** dan **`FFDEMO000002`**.

---

## Instalasi Lokal

```bash
git clone https://github.com/USERNAME_ANDA/fleetflow.git
cd fleetflow
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Jalankan pengujian otomatis:
```bash
php vendor/bin/phpunit
```

---

## Deploy Online (Render / Railway)

Repositori ini sudah dilengkapi dengan `Dockerfile`, `docker/start.sh`, dan `render.yaml`:
1. Push repositori ini ke GitHub.
2. Buat layanan baru di **[Render.com](https://render.com)** (*New -> Blueprint* atau *Web Service -> Docker*) dan pilih repositori ini.
3. Set variabel lingkungan `APP_KEY` (dari `php artisan key:generate --show`), `APP_ENV=production`, dan `APP_DEMO=true`.
4. Klik **Deploy** — database akan otomatis termigrasi dan terisi data demo saat container menyala.

---

## Dokumentasi Lengkap
- [docs/TEKNIS.md](docs/TEKNIS.md) — Arsitektur sistem & daftar service.
- [docs/PANDUAN-PENGGUNA.md](docs/PANDUAN-PENGGUNA.md) — Panduan operasional per peran.
- [docs/PROGRESS.md](docs/PROGRESS.md) — Riwayat penyelesaian Fase 0–18.
