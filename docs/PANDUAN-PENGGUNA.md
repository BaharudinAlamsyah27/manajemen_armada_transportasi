# Panduan Pengguna — FleetFlow ERP

## 1. Akun Bawaan (Lokal)
Password bawaan seluruh akun seeder adalah `password`:
- **Admin Pusat:** `adminpusat` / `pusat@fleetflow.test`
- **Staf Keuangan:** `keuangan` / `keuangan@fleetflow.test`
- **Admin Cabang:** `adminjkt`, `adminbdg`, `adminsby`
- **Sopir:** `sopir1`, `sopir2` (wajib mengganti sandi saat login pertama)

## 2. Alur Operasional Harian (Admin Cabang / Pusat)
1. **Pesanan & Resi:** Buat pesanan baru di menu *Pesanan & Resi*. Resi acak `FF...` otomatis dibuat. Pelanggan dapat melacak via `/lacak/{kode_resi}` tanpa login.
2. **Penjadwalan Manifest:** Buka *Penjadwalan & Manifest -> Buat Manifest Baru*. Pilih kendaraan, sopir, tanggal, dan pesanan. Sistem otomatis menolak jika muatan berlebih, dokumen/SIM kedaluwarsa, SIM tidak sesuai, atau jam bentrok.
3. **Eksekusi & Verifikasi Lapangan:** Sopir menjalankan pengiriman melalui Portal Sopir (`/portal`), mengunggah foto bukti terima, serta mencatat BBM/biaya jalan. Admin cabang menyetujui/menolak input sopir di menu *Verifikasi Lapangan*.

## 3. Alur Keuangan & Penggajian (Staf Keuangan / Admin Pusat)
1. **Penagihan:** Buka *Penagihan & Invoice*, pilih pelanggan dan pesanan berstatus `terkirim` untuk menerbitkan invoice & mencatat pembayaran. Jurnal akuntansi otomatis terbentuk.
2. **Akuntansi:** Buka *Akuntansi & Keuangan* untuk memantau Neraca Saldo, Laba Rugi, Neraca, membuat jurnal manual (dengan persetujuan maker-checker), atau mengunduh PDF.
3. **Penggajian:** Catat kehadiran di *Absensi*, lalu buka *Penggajian* untuk menghitung slip otomatis berdasarkan hari hadir, jumlah ritase selesai, dan cicilan kasbon. Setelah disetujui dan dibayar, slip tampil di Portal Sopir.
