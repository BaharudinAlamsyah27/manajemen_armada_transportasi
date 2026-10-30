# MODUL PORTAL SOPIR, PENGUMUMAN, ABSENSI, BUKTI PENGIRIMAN, DAN GPS

Rujukan: `@FLEETFLOW-GUIDE.md` (bagian 0, asumsi A2, A5, A13, A19-A22, peran bagian 5). Dikerjakan pada **Fase 8** (Portal Sopir), **Fase 9** (Pengumuman & Absensi), **Fase 15** (GPS).

---

## 1. Tujuan

Memberi sopir akses terbatas dan aman untuk: melihat jadwal, menjalankan pengiriman di lapangan, mencatat BBM/biaya jalan, membaca pengumuman, melihat slip gaji sendiri, dan (Fase 15) membagikan lokasi selama bertugas. Admin mendapat data lapangan yang lebih cepat, dengan verifikasi sebelum masuk laporan dan pembukuan.

---

## 2. Akun & Autentikasi Sopir

- Peran `sopir`; `users.karyawan_id` (unik) menautkan akun ke data karyawan berjabatan `sopir`. Akun dibuat oleh `admin_pusat`/`admin_cabang` dari halaman karyawan.
- **Login memakai `username` atau email** (`users.username` unik; sopir tidak wajib punya email). Sesuaikan form login Breeze; rate limit tetap berlaku.
- Kata sandi awal acak; **`wajib_ganti_password = true`** sampai diganti pada login pertama. Reset oleh admin memaksa ganti lagi.
- Akun otomatis nonaktif bila karyawan `aktif = false`; sesi berjalan langsung ditolak pada permintaan berikutnya.
- Setelah login, `sopir` selalu diarahkan ke portal dan **tidak boleh** membuka rute admin (middleware terpisah + Policy).
- **Pembatasan data:** semua query portal dibatasi oleh `karyawan_id` pengguna (bukan hanya `cabang_id`). Akses ke objek milik sopir lain via URL langsung menghasilkan 404/403 (diuji otomatis).

---

## 3. Skema Tambahan

| Tabel / kolom | Isi |
|---|---|
| `users.username`, `users.karyawan_id`, `users.wajib_ganti_password` | lihat bagian 2 |
| `bukti_pengirimans` | pesanan_id, pengiriman_id, jenis (`foto`,`tanda_tangan`), path (disk privat), nama_penerima, catatan, diambil_oleh, waktu, latitude/longitude (nullable), ukuran, mime |
| `pengumumans` | cabang_id (null = semua cabang), judul, isi, kategori (`info`,`keselamatan`,`kebijakan`,`darurat`), target_peran (json: `sopir`, `admin_cabang`, ...), disematkan (bool), terbit_pada, kedaluwarsa_pada, dibuat_oleh |
| `pengumuman_dibacas` | pengumuman_id, user_id, dibaca_pada. Unique (pengumuman_id, user_id) |
| `absensis` | cabang_id, karyawan_id, tanggal, status (`hadir`,`izin`,`sakit`,`alpa`,`libur`), catatan, dicatat_oleh. Unique (karyawan_id, tanggal) |
| `bbm_logs`, `biaya_operasionals` (kolom baru) | `sumber` (`admin`,`sopir`), `status_verifikasi` (`terverifikasi`,`menunggu_verifikasi`,`ditolak`), `diverifikasi_oleh`, `diverifikasi_pada`, `alasan_tolak`, `bukti_path` |
| `pesanan_status_logs` (kolom baru) | `sumber` (`admin`,`sopir`,`sistem`), latitude/longitude (nullable) |
| `sesi_pelacakan` (Fase 15) | pengiriman_id, kendaraan_id, sopir_id, mulai, selesai, status (`aktif`,`selesai`,`terputus`), persetujuan_sopir_pada |
| `lokasi_pings` (Fase 15) | sesi_id, kendaraan_id, latitude, longitude, akurasi_m, kecepatan_kmh, arah, waktu_perangkat, waktu_server, sumber (`web`,`traccar`,`lain`), valid (bool). Index (kendaraan_id, waktu_server) |
| `kendaraans.traccar_device_id` (nullable) | pemetaan bila memakai Traccar |

---

## 4. Portal Sopir (mobile-first, layout terpisah)

Navigasi bawah (5 tab): **Beranda, Jadwal, Pengumuman, Slip Gaji, Profil.** Tombol besar, satu tangan, kontras tinggi, indikator memuat, pesan galat Bahasa Indonesia, dapat dipasang di layar utama (web app manifest + ikon; offline penuh **tidak** diwajibkan).

### 4.1 Layar dan aturan

| Layar | Isi | Aturan |
|---|---|---|
| **Beranda** | Pengiriman hari ini (kartu besar: kode, kendaraan, jam mulai, jumlah pesanan), pengumuman baru/darurat, peringatan dokumen SIM mendekati kedaluwarsa | hanya data milik sopir |
| **Jadwal** | Daftar 7 hari ke depan dan riwayat 30 hari (paginasi) | - |
| **Detail Pengiriman** | Daftar pesanan berurutan: penerima, alamat, catatan barang, tombol **Buka Peta** (tautan ke aplikasi peta dengan alamat) dan **Telepon** (`tel:`) | nomor telepon penerima tampil penuh **hanya** untuk pengiriman `terjadwal`/`berjalan` pada hari itu; selain itu disamarkan |
| **Mulai Perjalanan** | Input `odometer_awal` (opsional foto odometer) | odometer >= `kendaraan.odometer_terakhir`; hanya pengiriman `terjadwal` milik sopir; mengubah status sesuai 6.3 |
| **Tandai Terkirim** (per pesanan) | Nama penerima aktual, foto bukti (wajib), catatan; tanda tangan opsional | hanya pesanan `dalam_perjalanan` pada pengiriman `berjalan` miliknya; waktu diambil dari **server** |
| **Gagal Kirim** (per pesanan) | Alasan (pilihan + catatan) | alasan wajib |
| **Selesai Pengiriman** | Input `odometer_akhir` | semua pesanan harus sudah final; `odometer_akhir >= odometer_awal` |
| **Catat BBM** | Tanggal, liter, harga/liter, odometer, tangki penuh, SPBU, **foto struk** | masuk sebagai `menunggu_verifikasi`; total dihitung server |
| **Catat Biaya Jalan** | Jenis (tol/parkir/lainnya), jumlah, foto bukti | `menunggu_verifikasi` |
| **Pengumuman** | Daftar (disematkan di atas, **darurat** berwarna mencolok), buka = tanda dibaca | hanya yang ditargetkan ke cabang/peran sopir |
| **Slip Gaji** | Daftar per bulan, detail rincian, unduh PDF | hanya slip milik sopir berstatus `dipublikasikan` |
| **Profil** | Data diri, SIM (nomor disamarkan, masa berlaku), ganti kata sandi | tidak dapat mengubah data karyawan |

### 4.2 Aturan lintas layar

- Sopir **tidak** dapat: membuat/mengubah jadwal, mengubah pesanan yang bukan miliknya, membatalkan pesanan, mengubah status di luar transisi yang diizinkan di tabel 4.1, atau melihat data keuangan/pesanan lain.
- Semua aksi tercatat di `pesanan_status_logs`/activity log dengan `sumber = sopir` dan waktu server; koordinat (bila izin lokasi diberikan) ikut disimpan tetapi **tidak wajib** (aksi tidak boleh gagal hanya karena lokasi ditolak).
- Cegah kirim ganda (tombol nonaktif saat proses + pemeriksaan idempotensi di server).
- Semua aksi memakai CSRF dan mengembalikan pesan yang jelas bila sesi habis (tanpa 419 mentah; arahkan ke login dengan pesan).

### 4.3 Verifikasi input lapangan (admin cabang)

Halaman "Verifikasi" untuk BBM dan biaya berstatus `menunggu_verifikasi`: lihat foto bukti, ubah nilai bila keliru (perubahan tercatat), **Setujui** atau **Tolak** (alasan wajib). Hanya yang `terverifikasi` masuk laporan, perhitungan km/L, dan jurnal (bila modul keuangan aktif). Dashboard menampilkan jumlah antrean verifikasi.

---

## 5. Bukti Pengiriman (POD) & Berkas

- Unggahan: JPEG/PNG, maks. 2 MB (kompres/perkecil di sisi klien bila memungkinkan), validasi MIME **dan** ekstensi di server, nama file diacak.
- Simpan di **disk privat** (bukan `public/`); akses lewat rute terotorisasi atau URL bertanda tangan berumur pendek. Tidak ada daftar direktori terbuka.
- Admin melihat bukti di detail pesanan dan timeline status. Bukti tidak dapat diganti setelah pesanan `terkirim` (koreksi oleh `admin_pusat` dengan alasan).
- Kebijakan retensi berkas dapat diatur (default 1 tahun setelah pesanan final).

---

## 6. Pengumuman ("Berita")

- Dibuat oleh `admin_pusat` (semua cabang atau cabang tertentu) dan `admin_cabang` (cabangnya). Kategori `darurat` ditampilkan menonjol dan disematkan otomatis.
- Target per peran; tanggal terbit dan kedaluwarsa; edit tercatat di riwayat.
- **Tanda dibaca:** admin melihat siapa yang sudah/belum membaca (persentase dan daftar).
- Tanpa notifikasi eksternal di MVP; indikator "baru" (lencana) pada tab Pengumuman dan Beranda.

---

## 7. Absensi (sederhana)

Admin cabang mencatat status harian karyawan (satu klik per baris, massal per tanggal). Dipakai untuk uang makan pada penggajian (`MODUL-KEUANGAN.md` bagian 7). Sopir melihat rekap kehadiran bulan berjalan miliknya (baca-saja). Absensi berbasis lokasi/selfie di luar MVP.

---

## 8. Slip Gaji di Portal

Disediakan oleh Fase 13; portal hanya membaca `slip_gajis` milik `karyawan_id` pengguna dengan status periode `dipublikasikan`. Nomor rekening disamarkan. Akses ke slip milik orang lain harus 404/403 dan diuji.

---

## 9. GPS — Rancangan dan Tahapan

### 9.1 Pilihan dan trade-off (jawab keputusan pemilik)

| Opsi | Biaya | Kelebihan | Keterbatasan |
|---|---|---|---|
| **A. Ponsel sopir via portal web** (Geolocation API) | Nol perangkat tambahan (memakai ponsel dan paket data sopir) | Cepat, sudah terintegrasi dengan portal dan sesi pengiriman | Umumnya andal saat halaman terbuka dan layar aktif; pelacakan latar belakang di browser sering dibatasi oleh Android/iOS; wajib HTTPS; boros baterai |
| **B. Traccar (server sumber terbuka) + Traccar Client di ponsel** | Server sendiri (VPS kecil) atau layanan host; aplikasi klien gratis | Berjalan di latar belakang, mendukung banyak jenis perangkat, dapat memakai ponsel sekarang dan perangkat keras nanti | Perlu server tambahan dan perawatan; aplikasi terpisah dari portal |
| **C. Perangkat GPS keras / langganan penyedia** | Perangkat sekitar di bawah Rp1,5 juta atau langganan mulai sekitar Rp99 ribu per bulan per unit (angka indikatif, cek harga terbaru) | Paling andal, terpasang di kendaraan (bukan bergantung sopir) | Biaya per kendaraan, integrasi bergantung API penyedia |

**Keputusan rancangan:** bangun **abstraksi `LokasiProvider`** sekarang, implementasikan **Opsi A** di Fase 15, dan sediakan titik integrasi untuk **Opsi B/C** (penerima webhook/pull API yang memetakan perangkat ke `kendaraan_id`). Dengan begitu peta, riwayat rute, dan laporan tidak berubah saat sumber data diganti.

### 9.2 Arsitektur

- `App\Contracts\LokasiProvider` -> `WebGeolocationProvider` (Fase 15), `TraccarProvider` (opsional, tidak dikerjakan kecuali diminta).
- `LokasiService::catat(sesi, data)` memvalidasi dan menyimpan ping; `SesiPelacakanService` membuka sesi saat pengiriman **dimulai** dan menutupnya saat **selesai/dibatalkan** (atau otomatis `terputus` bila tidak ada ping > setting `gps_timeout_menit`, default 15).
- **Pelacakan hanya aktif selama pengiriman `berjalan`** dan **setelah persetujuan sopir** (dialog persetujuan yang dicatat: waktu dan versi teks kebijakan). Sopir melihat indikator "Lokasi aktif" dan tombol jeda/berhenti yang tercatat.
- Klien portal: `navigator.geolocation.watchPosition`, kirim ping tiap `gps_interval_detik` (default 30; 15-120), **buffer** bila sinyal hilang lalu kirim serentak (maks. 20 titik), minta **Screen Wake Lock** bila didukung, dan tampilkan peringatan bila izin lokasi ditolak/dimatikan. Kirim dengan `fetch` + header CSRF; tangani sesi habis dengan pesan (bukan gagal diam-diam).
- Endpoint `POST /portal/lokasi`: hanya `sopir` pemilik sesi aktif; **rate limit** (mis. 6 permintaan/menit); validasi: lat -90..90, lng -180..180, `akurasi_m` numerik, `waktu_perangkat` tidak lebih dari +/- 5 menit dari waktu server (di luar itu tandai `valid = false`), kecepatan >= 0; titik dengan akurasi buruk (> `gps_akurasi_maks_m`, default 100) disimpan tetapi ditandai tidak valid dan tidak dipakai menghitung jarak.
- **Jarak tempuh GPS** = jumlah jarak Haversine antar titik valid berurutan; dibandingkan dengan selisih odometer pengiriman; selisih di luar `gps_toleransi_persen` (default 20) ditandai **anomali** (peringatan, bukan penolakan).
- **Peta Armada (admin):** Leaflet + OpenStreetMap (periksa kebijakan penggunaan tile; pertimbangkan penyedia tile bila trafik tinggi; tampilkan atribusi), marker kendaraan bertugas (warna menurut status, waktu update terakhir), klik untuk detail pengiriman/sopir, **polling** 10-15 detik (websocket ditunda), filter cabang (pusat), dan halaman **Riwayat Rute** per pengiriman (garis rute + titik berhenti > 5 menit).
- **Geofence sederhana (opsional):** titik tujuan pesanan (koordinat dari alamat bila tersedia) dan penanda "tiba di sekitar tujuan" (radius setting) sebagai info di timeline, bukan pengubah status otomatis.
- **Retensi:** ping mentah disimpan `gps_retensi_hari` (default 90) lalu dihapus oleh scheduler (`schedule:run` via cron); ringkasan (jarak, waktu, titik berhenti) per sesi tetap disimpan.

### 9.3 Privasi dan keamanan lokasi

- Lokasi adalah data pribadi sopir: hanya `admin_pusat` dan `admin_cabang` (cabangnya) yang dapat melihat; **halaman pelacakan publik tidak menampilkan posisi**, hanya status.
- Pelacakan **hanya saat bertugas**; tidak ada pelacakan di luar sesi pengiriman.
- Persetujuan sopir tercatat; kebijakan (tujuan, jam pelacakan, retensi, siapa yang melihat) tertulis dan mudah diakses di portal.
- Akses ke Peta dan Riwayat Rute tercatat di activity log. Semua data lokasi lewat HTTPS.

---

## 10. Fase (kriteria selesai = KS)

### Fase 8 — Portal Sopir
Layout portal mobile-first, akun/login sopir (username, wajib ganti sandi), Beranda, Jadwal, Detail Pengiriman, aksi lapangan (mulai, terkirim dengan foto, gagal, selesai), Catat BBM/Biaya Jalan dengan status verifikasi, halaman Verifikasi untuk admin cabang, bukti pengiriman (disk privat), Profil. Slip Gaji dan Pengumuman berupa halaman kerangka sampai fase terkait.
**KS:** test: sopir hanya melihat/mengubah pengiriman miliknya; sopir tidak bisa membuka rute admin; transisi status di luar aturan ditolak; nomor telepon penerima hanya penuh pada pengiriman aktif hari itu; input BBM/biaya sopir tidak masuk laporan sebelum diverifikasi; unggahan menolak berkas bukan gambar/terlalu besar; bukti tersimpan di disk privat; sesi habis menampilkan pesan yang jelas; tampilan diuji pada lebar ponsel.

### Fase 9 — Pengumuman & Absensi
Pengumuman (target cabang/peran, kategori, sematan, kedaluwarsa, tanda dibaca dan rekap pembaca), Absensi (input massal per tanggal, rekap bulanan), tab Pengumuman dan rekap kehadiran di portal.
**KS:** sopir hanya melihat pengumuman yang ditargetkan kepadanya; admin cabang tidak bisa mengumumkan ke cabang lain; rekap pembaca benar; absensi unik per karyawan per tanggal.

### Fase 15 — GPS (Opsi A)
Abstraksi `LokasiProvider`, `WebGeolocationProvider`, `sesi_pelacakan`, `lokasi_pings`, dialog persetujuan sopir, klien pelacakan di portal, endpoint dengan rate limit dan validasi, Peta Armada, Riwayat Rute, perbandingan jarak GPS vs odometer, retensi via scheduler, dokumentasi keterbatasan (latar belakang, baterai, HTTPS).
**KS:** test: ping hanya diterima dari sopir pemilik sesi aktif; ping di luar sesi/di luar batas validasi ditolak atau ditandai; rate limit bekerja; jarak Haversine benar pada dataset uji; sesi menjadi `terputus` setelah timeout; admin cabang tidak melihat lokasi cabang lain; halaman publik tidak menampilkan koordinat; data lebih tua dari retensi terhapus; berjalan di HTTPS (uji manual di ponsel) dan pesan jelas muncul bila izin lokasi ditolak.

---

## 11. Pengujian Wajib (ringkasan modul)

Pembatasan data sopir (semua objek); rute admin tertutup bagi sopir; akun nonaktif ditolak; wajib ganti kata sandi awal; transisi status oleh sopir; verifikasi BBM/biaya memengaruhi laporan hanya setelah disetujui; berkas privat dan validasi unggahan; targeting pengumuman; slip hanya pemiliknya; semua aspek GPS di Fase 15.
