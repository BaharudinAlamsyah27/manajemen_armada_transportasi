# FleetFlow — Pedoman Proyek untuk Agent

> Dokumen ini adalah **sumber kebenaran tunggal**. Baca bagian 0-3 sebelum mengerjakan apa pun; buka bagian lain hanya saat dibutuhkan. Bila instruksi pengguna bertentangan dengan dokumen ini, tanyakan dulu, lalu perbarui dokumen ini setelah diputuskan.
>
> **Versi 2.** Modul rinci: `docs/MODUL-KEUANGAN.md` (akuntansi, penagihan, hutang, aset, penggajian, laporan keuangan) dan `docs/MODUL-SOPIR-GPS.md` (portal sopir, pengumuman, absensi, bukti pengiriman, GPS). Salin keduanya ke folder `docs/` dan panggil dengan `@` hanya pada fase yang memakainya.

---

## 0. Cara Kerja Agent (WAJIB)

1. **Satu fase per permintaan.** Jangan lompat ke fase berikutnya sebelum pengguna menyuruh.
2. **Audit dulu, tulis kemudian.** Baca file yang sudah ada; jangan membuat ulang yang sudah berfungsi.
3. Alur: audit -> rencana singkat (daftar file dibuat/diubah) -> tunggu persetujuan -> kerjakan.
4. **Jangan mengubah file di luar lingkup fase.** Masalah lain dicatat di `docs/PROGRESS.md` bagian "Catatan/Masalah".
5. Setelah selesai: `php artisan migrate:fresh --seed` (bila DB berubah), `php artisan test`, `npm run build`, perbarui `docs/PROGRESS.md`, siapkan pesan commit (`fase-N: ringkasan`; jangan commit sebelum diminta), lalu laporkan **maksimal 10 baris**: apa dibuat, cara uji, yang belum selesai.
6. **Hemat token:** jangan mencetak ulang file panjang; tampilkan hanya potongan yang berubah.
7. **Jangan mengarang aturan bisnis.** Aturan yang tidak tercantum: tanya (maks. 3 pertanyaan sekaligus) atau ambil asumsi paling sederhana dan tulis di `docs/ASUMSI.md`.
8. Antarmuka, pesan validasi, dan label memakai **Bahasa Indonesia**. Nama tabel/kolom/kelas memakai bahasa Indonesia seperti di dokumen ini.
9. Uang: `decimal(15,2)`, tampil `Rp 1.250.000`. Jarak: kilometer (integer). Volume BBM: liter `decimal(8,2)`. Berat: kg `decimal(10,2)`. Zona waktu `Asia/Jakarta`. **Dilarang memakai float untuk uang.**

---

## 1. Ringkasan Proyek

**FleetFlow** adalah aplikasi web manajemen armada dan pengiriman untuk perusahaan logistik kecil dengan banyak cabang. Satu aplikasi, satu database, banyak cabang. Pengguna: admin pusat, staf keuangan, admin cabang, dan sopir.

### Cakupan dan rilis

Pengerjaan dibagi tiga rilis. **Jangan memulai rilis berikutnya sebelum semua Kriteria Selesai rilis sebelumnya terpenuhi.**

| Rilis | Fase | Isi |
|---|---|---|
| **1. Operasional inti** | 0-9 | Master data, pesanan dan status, penjadwalan multi-rit, BBM dan biaya, servis dan suku cadang, portal sopir, pengumuman, absensi |
| **2. Keuangan & penggajian** | 10-14 | Akuntansi berpasangan, penagihan dan piutang, hutang usaha, aset dan penyusutan, penggajian, laporan keuangan profesional |
| **3. Pelacakan, dasbor, pengerasan** | 15-18 | GPS, dasbor dan laporan operasional, riwayat perubahan, pelacakan publik, pengerasan keamanan, dokumentasi, deploy |

Fitur inti: dashboard operasional; CRUD kendaraan dan karyawan; pesanan dan pelacakan status; penjadwalan pengiriman dan penugasan sopir; pencatatan BBM dan servis; stok suku cadang; laporan biaya dan performa armada; laporan keuangan; login multi-peran; validasi; riwayat perubahan; desain responsif; dokumentasi teknis dan panduan pengguna.

### Di luar lingkup (JANGAN dikerjakan kecuali diminta)

Aplikasi native Android/iOS untuk sopir, optimasi rute otomatis, integrasi pembayaran online/bank, notifikasi WhatsApp/email/push, serah-terima atau transfer antar cabang (pesanan, kendaraan, stok), perhitungan otomatis PPh 21/BPJS, pelaporan pajak (faktur pajak elektronik, SPT), integrasi perangkat GPS keras/Traccar (Fase 15 hanya menyiapkan antarmuka), otentikasi dua faktor, pembukuan antar-perusahaan (intercompany), mata uang asing, portal pelanggan.

---

## 2. Asumsi Bisnis (dapat diubah pengguna; catat perubahan di `docs/ASUMSI.md`)

| # | Asumsi | Dampak bila salah |
|---|---|---|
| A1 | Kendaraan, karyawan, dan pesanan **dimiliki satu cabang**. Pengiriman hanya memakai kendaraan, sopir, dan pesanan dari cabang yang sama. | Perlu fitur serah-terima/pinjam antar cabang |
| A2 | **Sopir memiliki akun login** dan mengakses portal khusus (jadwal, aksi lapangan, BBM/biaya jalan, pengumuman, slip gaji sendiri, profil, dan lokasi bila GPS aktif). Kernet, mekanik, dan staf **tanpa akun** (hanya data karyawan). | Peran tambahan ditambah lewat spatie tanpa ubah skema |
| A3 | **4 peran login:** `admin_pusat` (semua cabang), `keuangan` (di pusat: akuntansi, penagihan, penggajian, laporan keuangan; bila belum dibutuhkan, fungsinya dipegang `admin_pusat`), `admin_cabang` (hanya cabangnya), `sopir` (hanya data miliknya). | - |
| A4 | Satu pesanan dimuat dalam **satu pengiriman aktif** pada satu waktu. Satu pengiriman boleh memuat banyak pesanan. | Perlu tabel pivot pesanan-pengiriman |
| A5 | Satu kendaraan/sopir **boleh beberapa pengiriman (rit) per hari** selama jendela waktu `[waktu_mulai_rencana, estimasi_tiba]` ditambah jeda `jeda_antar_rit_menit` (default 30) **tidak tumpang tindih**. Total jam kerja sopir per hari di atas `maks_jam_kerja_sopir` (default 12) hanya **peringatan**; nilai disesuaikan kebijakan dan regulasi perusahaan. | Batas jam kerja dapat dijadikan penolakan |
| A6 | Pengiriman **ditolak** bila muatan melebihi `kapasitas_kg` kendaraan. | Bisa dijadikan peringatan |
| A7 | Pengiriman **ditolak** bila STNK, KIR, atau pajak kendaraan kedaluwarsa pada tanggal rencana, atau SIM sopir kedaluwarsa/tidak sesuai jenis kendaraan. `admin_pusat` boleh menimpa dengan alasan tertulis (tercatat). *(Disetujui pemilik.)* | - |
| A8 | Kesesuaian SIM (sederhana): motor -> C; pickup/box -> B1 atau B2; truk -> B2. | Sesuaikan kebijakan/regulasi |
| A9 | Tarif pesanan diusulkan dari master tarif (kota tujuan x berat, ada tarif minimum) dan boleh ditimpa dengan alasan tercatat. Penagihan, invoice, dan piutang ada di modul keuangan. | - |
| A10 | Efisiensi BBM memakai metode **tangki penuh ke tangki penuh** (km/L). | Metode lain butuh data tambahan |
| A11 | **Biaya armada operasional** = BBM + servis (jasa + suku cadang) + biaya operasional lain; **tidak** termasuk gaji. Laporan keuangan formal memuat gaji, penyusutan, dan beban lain. | - |
| A12 | Master suku cadang **global**; stok dicatat **per cabang**; pemakaian lewat servis atau penyesuaian. | Perlu transfer antar cabang |
| A13 | Pelacakan = **timeline status** + **GPS lewat ponsel sopir** (Fase 15, tampil di Peta Armada untuk admin). Sumber data diabstraksikan (`LokasiProvider`) agar dapat diganti Traccar/perangkat keras kelak. | Perlu biaya perangkat/langganan |
| A14 | Data keuangan dan transaksi **tidak dihapus permanen**. Jurnal terposting tidak diedit; koreksi lewat jurnal balik. | - |
| A15 | Hosting belum ditentukan; **HTTPS wajib** di produksi (sesi aman dan GPS memerlukannya). Panduan deploy generik (cPanel dan VPS). | - |
| A16 | Akuntansi **akrual**, mata uang IDR, bagan akun mengacu SAK EMKM dan **wajib dikonfirmasi akuntan**. PPN/PPh **dapat dikonfigurasi, default 0**; agent dilarang menetapkan tarif sendiri. | Perlu penyesuaian setelah konsultasi pajak |
| A17 | Penggajian bulanan dengan komponen: gaji pokok, tunjangan, uang makan (per hari hadir), uang jalan/ritase (per pengiriman selesai), lembur/bonus manual, potongan kasbon/denda/lain. **PPh 21 dan BPJS tidak dihitung otomatis** di MVP (komponen manual). | Otomatisasi butuh keputusan dan aturan dari akuntan |
| A18 | Input BBM/biaya dari sopir berstatus `menunggu_verifikasi` sampai disetujui admin cabang; baru sesudahnya masuk laporan dan jurnal. | - |
| A19 | Bukti pengiriman berupa **foto** (wajib bagi sopir saat menandai `terkirim`); admin yang menginput manual boleh tanpa foto dengan catatan. Berkas disimpan privat. | - |
| A20 | "Berita" = **pengumuman** (target cabang/peran, kategori, tanda dibaca). Absensi sederhana harian dicatat admin cabang (dipakai untuk uang makan). | - |
| A21 | Lokasi hanya dilacak **selama pengiriman berjalan**, dengan **persetujuan sopir**, retensi ping mentah 90 hari, dan hanya dilihat admin. Halaman publik tidak menampilkan posisi. | Perlu kebijakan privasi tertulis |
| A22 | Master pelanggan (perusahaan/customer) global untuk penagihan; pesanan boleh dari pengirim non-master. | - |

---

## 3. Keputusan Teknis Final

- **Lokal:** Laragon (Windows), PHP 8.3, MySQL, Node LTS, Composer.
- **Backend:** Laravel 11. **Frontend:** Blade + Tailwind CSS + Alpine.js (Vite). Grafik: Chart.js. **Tanpa Filament/Nova.**
- **Paket:** `laravel/breeze` (Blade), `spatie/laravel-permission`, `spatie/laravel-activitylog`, `maatwebsite/excel` (ekspor Excel/CSV; boleh diganti ekspor CSV native bila bermasalah di hosting), `barryvdh/laravel-dompdf` (PDF: invoice, kwitansi, slip gaji, laporan keuangan). Peta (Fase 15): Leaflet + OpenStreetMap.
- **Pengujian:** PHPUnit/Pest (bawaan Laravel).
- **Struktur kode:**
  - `app/Services/`: `NomorDokumenService`, `PesananStatusService`, `PenjadwalanService`, `PengirimanService`, `StokSukuCadangService`, `BiayaArmadaService`, `KinerjaArmadaService`, `JurnalService`, `JurnalOtomatisService`, `PenagihanService`, `PayrollService`, `LaporanKeuanganService`, `LokasiService`, `SesiPelacakanService` (modul di `docs/`); kontrak `app/Contracts/LokasiProvider`
  - `app/Models/Concerns/BelongsToCabang.php`
  - `app/Policies/`, `app/Http/Requests/`, `app/Enums/` (status sebagai enum PHP)
  - `docs/`: `PROGRESS.md`, `ASUMSI.md`, `TEKNIS.md`, `PANDUAN-PENGGUNA.md`
- **Multi-cabang:** kolom `cabang_id` pada semua tabel bercabang; trait global scope:

```php
trait BelongsToCabang {
    protected static function bootBelongsToCabang() {
        static::addGlobalScope('cabang', function ($q) {
            $u = auth()->user();
            if ($u && !$u->hasRole('admin_pusat')) {
                $q->where($q->getModel()->getTable().'.cabang_id', $u->cabang_id);
            }
        });
        static::creating(function ($m) {
            if (!$m->cabang_id && auth()->check() && auth()->user()->cabang_id) {
                $m->cabang_id = auth()->user()->cabang_id;
            }
        });
    }
}
```

`admin_pusat` memiliki `cabang_id = null`, **wajib memilih cabang** di form pembuatan data, dan punya filter cabang di semua daftar dan laporan. Dilarang memakai `withoutGlobalScopes()` kecuali untuk `admin_pusat` dengan komentar alasan.

---

## 4. Skema Database

Semua tabel: `id`, `timestamps`. Tabel bisnis juga `softDeletes`. Foreign key wajib. Status memakai enum PHP + kolom string. Indeks disebut di kolom "catatan".

| Tabel | Kolom penting | Catatan |
|---|---|---|
| `cabangs` | kode (unique), nama, kota, alamat, no_hp, aktif | |
| `users` | name, **username (unique)**, email (unique, nullable), password, cabang_id (nullable), **karyawan_id (unique, nullable; untuk sopir)**, **wajib_ganti_password**, aktif | peran via spatie |
| `karyawans` | cabang_id, kode, nama, jabatan (`sopir`,`kernet`,`mekanik`,`staf_gudang`,`staf_admin`), no_hp, **no_sim (cast encrypted)**, jenis_sim, sim_berlaku_sampai, aktif | unique (cabang_id, kode) |
| `kendaraans` | cabang_id, kode, plat_nomor (unique), jenis (`motor`,`pickup`,`box`,`truk`), merk, model, tahun, kapasitas_kg, jenis_bbm, odometer_terakhir, status (`tersedia`,`bertugas`,`servis`,`nonaktif`), stnk_berlaku_sampai, kir_berlaku_sampai, pajak_berlaku_sampai | index (cabang_id, status) |
| `pesanans` | cabang_id, pelanggan_id (nullable), **kode_resi (unique, acak)**, tanggal_pesan, nama_pengirim, **no_hp_pengirim (encrypted)**, alamat_asal, kota_asal, nama_penerima, **no_hp_penerima (encrypted)**, alamat_tujuan, kota_tujuan, deskripsi_barang, berat_kg, jumlah_koli, tarif, status, pengiriman_id (nullable), token_publik (nullable, acak), dibuat_oleh | index (cabang_id, status), (kode_resi) |
| `pesanan_status_logs` | pesanan_id, status_lama, status_baru, catatan, user_id, waktu | append-only, tidak boleh diubah/dihapus |
| `pengirimans` | cabang_id, kode (unique), tanggal_rencana, kendaraan_id, sopir_id (karyawan), kernet_id (nullable), tujuan_ringkas, waktu_mulai_rencana, estimasi_tiba, rit_ke, odometer_awal, odometer_akhir, waktu_berangkat, waktu_tiba, status (`terjadwal`,`berjalan`,`selesai`,`batal`), catatan, dibuat_oleh, override_alasan (nullable) | index (kendaraan_id, waktu_mulai_rencana), (sopir_id, waktu_mulai_rencana) |
| `bbm_logs` | cabang_id, kendaraan_id, sopir_id (nullable), pengiriman_id (nullable), tanggal, liter, harga_per_liter, total, odometer, tangki_penuh (bool), spbu, dicatat_oleh | index (kendaraan_id, tanggal) |
| `servis_kendaraans` | cabang_id, kendaraan_id, jenis (`berkala`,`perbaikan`,`ganti_ban`,`kir`,`lainnya`), status (`dijadwalkan`,`dikerjakan`,`selesai`,`batal`), tanggal_masuk, tanggal_selesai, odometer, bengkel, biaya_jasa, total_biaya, deskripsi, servis_berikutnya_km, servis_berikutnya_tanggal | |
| `servis_suku_cadangs` | servis_id, suku_cadang_id, qty, harga_satuan | |
| `suku_cadangs` | kode (unique), nama, satuan, harga_standar, stok_minimum, aktif | global |
| `stok_suku_cadangs` | cabang_id, suku_cadang_id, qty | unique (cabang_id, suku_cadang_id) |
| `mutasi_suku_cadangs` | cabang_id, suku_cadang_id, tipe (`masuk`,`keluar_servis`,`penyesuaian`), qty, harga_satuan, referensi_type, referensi_id, keterangan, user_id, tanggal | append-only |
| `biaya_operasionals` | cabang_id, kendaraan_id (nullable), pengiriman_id (nullable), jenis (`tol`,`parkir`,`denda`,`lainnya`), tanggal, jumlah, keterangan, dicatat_oleh | |
| `nomor_dokumens` | cabang_id, jenis, periode, terakhir | unique (cabang_id, jenis, periode) |
| `settings` | key (unique), value | |
| `activity_log` (spatie) | + kolom `cabang_id` (nullable) | dibaca-saja di UI |

**Tabel modul** (dibuat pada fasenya; struktur lengkap di modul): keuangan dan penggajian (`akuns`, `periode_akuntansis`, `jurnals`, `jurnal_details`, `pelanggans`, `tarifs`, `invoices`, `invoice_items`, `pembayarans`, `pembayaran_alokasis`, `pemasoks`, `tagihan_pemasoks`, `pembayaran_pemasoks`, `aset_tetaps`, `penyusutans`, `anggarans`, `komponen_gajis`, `profil_gajis`, `periode_gajis`, `slip_gajis`, `slip_gaji_items`, `kasbons`) -> `docs/MODUL-KEUANGAN.md` bagian 2; portal sopir dan GPS (`bukti_pengirimans`, `pengumumans`, `pengumuman_dibacas`, `absensis`, `sesi_pelacakan`, `lokasi_pings`, kolom verifikasi pada `bbm_logs`/`biaya_operasionals`) -> `docs/MODUL-SOPIR-GPS.md` bagian 3.

**Seeder wajib:** 3 cabang contoh, 1 `admin_pusat`, 1 `keuangan`, 1 `admin_cabang` per cabang, beberapa karyawan tiap jabatan (dan 2 akun `sopir` contoh), kendaraan campuran, suku cadang contoh, settings default. Kata sandi seeder hanya untuk lokal dan wajib diganti sebelum deploy.

---

## 5. Peran & Akses

| Peran | Akses |
|---|---|
| `admin_pusat` | Semua menu dan cabang; Data Cabang; Akun & Akses; Riwayat Perubahan semua cabang; boleh menimpa aturan A7 dengan alasan; koreksi data final; membuka kembali periode akuntansi |
| `keuangan` | Modul keuangan penuh (jurnal, penagihan semua cabang, hutang, aset, penggajian, laporan keuangan, tutup periode); data operasional hanya baca; tidak mengelola akun/cabang |
| `admin_cabang` | Semua menu operasional **hanya cabangnya**: pesanan, penjadwalan, BBM/servis/stok, verifikasi input lapangan, pengumuman dan absensi cabang, membuat invoice dan mencatat pembayaran pelanggan cabangnya; **tidak** dapat mengakses jurnal, penggajian, laporan keuangan formal, atau mengelola cabang/akun |
| `sopir` | **Hanya portal sopir** dan hanya data miliknya (`users.karyawan_id`): jadwal dan pengiriman yang ditugaskan, aksi lapangan terbatas, BBM/biaya jalan (menunggu verifikasi), pengumuman, slip gaji sendiri berstatus `dipublikasikan`, profil, dan berbagi lokasi saat bertugas |

- Menu tampil sesuai izin, **dan** setiap route dilindungi middleware + Policy (menyembunyikan menu saja tidak cukup).
- Pembatasan `sopir` **tidak cukup dengan global scope cabang**: wajib Policy per objek berbasis `karyawan_id`. Sopir yang membuka rute admin diarahkan ke portal (403 untuk API).
- Akses ke data cabang lain lewat URL langsung menghasilkan 404/403 (diuji otomatis untuk semua peran).
- Pengguna nonaktif atau karyawan nonaktif tidak bisa login; sesi berjalan ditolak pada permintaan berikutnya.

---

## 6. Aturan Bisnis & Alur (INTI: harus benar)

### 6.1 Status pesanan (state machine, `PesananStatusService`)

| Dari | Ke | Syarat |
|---|---|---|
| `draf` | `menunggu_jadwal` | data wajib lengkap (alamat, penerima, berat > 0) |
| `draf`/`menunggu_jadwal`/`terjadwal`/`gagal_kirim` | `dibatalkan` | alasan wajib; tidak boleh saat `dalam_perjalanan` |
| `menunggu_jadwal` | `terjadwal` | hanya otomatis saat ditugaskan ke pengiriman |
| `terjadwal` | `menunggu_jadwal` | dilepas dari pengiriman (pengiriman dibatalkan/diubah) |
| `terjadwal` | `dalam_perjalanan` | hanya otomatis saat pengiriman dimulai |
| `dalam_perjalanan` | `terkirim` | wajib: nama penerima aktual + waktu diterima |
| `dalam_perjalanan` | `gagal_kirim` | wajib alasan |
| `gagal_kirim` | `menunggu_jadwal` | dijadwalkan ulang (dilepas dari pengiriman lama) |

- `terkirim` dan `dibatalkan` **final**. Koreksi hanya `admin_pusat`, dengan alasan, dan tercatat.
- **Sopir** hanya boleh: `terjadwal` -> `dalam_perjalanan` (Mulai Perjalanan) serta `dalam_perjalanan` -> `terkirim`/`gagal_kirim`, dan hanya untuk pesanan pada pengiriman miliknya; `terkirim` oleh sopir mewajibkan foto bukti (A19).
- Setiap perubahan status ditulis ke `pesanan_status_logs` (append-only) dalam transaksi yang sama. Transisi di luar tabel ditolak dengan pesan jelas.
- `kode_resi`: `FF` + `YYMMDD` + 6 karakter acak (huruf besar/angka tanpa karakter membingungkan), unik, coba ulang bila bentrok. Bukan urutan berjalan agar tidak bisa ditebak.

### 6.2 Penjadwalan pengiriman & penugasan sopir (`PenjadwalanService`)

Dalam **satu `DB::transaction`** dengan `lockForUpdate` pada baris kendaraan dan sopir:

1. Kendaraan, sopir, kernet, dan semua pesanan berada di **cabang yang sama** (A1).
2. Kendaraan tidak berstatus `servis`/`nonaktif`, dan **jendela waktu** `[waktu_mulai_rencana, estimasi_tiba]` (ditambah `jeda_antar_rit_menit`, default 30) **tidak tumpang tindih** dengan pengiriman `terjadwal`/`berjalan` lain milik kendaraan yang sama; aturan sama untuk sopir dan kernet (A5). `rit_ke` terisi otomatis (urutan rit kendaraan pada hari itu). Total jam kerja sopir pada hari itu di atas `maks_jam_kerja_sopir` -> **peringatan** (bukan penolakan).
3. Sopir berjabatan `sopir`, `aktif`, SIM berlaku pada `tanggal_rencana`, dan jenis SIM sesuai jenis kendaraan (A8).
4. STNK, KIR, dan pajak kendaraan berlaku pada `tanggal_rencana` (A7).
5. Semua pesanan berstatus `menunggu_jadwal` dan **total berat <= kapasitas_kg** (A6).
6. Bila lolos: buat pengiriman (`terjadwal`), isi `pesanans.pengiriman_id`, ubah status pesanan menjadi `terjadwal` lewat `PesananStatusService`.
7. Penolakan mengembalikan **daftar semua alasan** sekaligus, bukan satu per satu. `admin_pusat` dapat menimpa butir 3-4 dengan `override_alasan` (wajib, dicatat di riwayat).

### 6.3 Menjalankan pengiriman (`PengirimanService`)

- **Mulai:** `odometer_awal >= kendaraan.odometer_terakhir`; kendaraan -> `bertugas`; pesanan -> `dalam_perjalanan`; isi `waktu_berangkat`.
- **Selesai:** semua pesanan pada pengiriman harus sudah `terkirim` atau `gagal_kirim`; `odometer_akhir >= odometer_awal`; kendaraan -> `tersedia`; `kendaraans.odometer_terakhir` diperbarui; isi `waktu_tiba`. Pesanan `gagal_kirim` dilepas (`pengiriman_id` = null) agar bisa dijadwalkan ulang.
- **Batal** (hanya saat `terjadwal`): pesanan dikembalikan ke `menunggu_jadwal`; alasan wajib.
- Pengiriman `selesai`/`batal` tidak dapat diedit (koreksi oleh `admin_pusat` dengan alasan).
- Aksi mulai/selesai juga dapat dilakukan **sopir yang ditugaskan** lewat portal (`docs/MODUL-SOPIR-GPS.md`); log mencatat `sumber = sopir`.

### 6.4 BBM (`bbm_logs`)

- `total = liter x harga_per_liter` **dihitung server**; input `total` dari browser diabaikan.
- `odometer >= ` odometer pencatatan BBM sebelumnya kendaraan itu dan `>= ` nilai terakhir kendaraan; kalau lebih kecil, tolak (kecuali koreksi `admin_pusat`).
- Efisiensi km/L (A10): antara dua pengisian `tangki_penuh = true` berturut-turut, `km = odometer_sekarang - odometer_penuh_sebelumnya`, `liter = jumlah liter semua pengisian setelah penuh sebelumnya sampai dan termasuk yang sekarang`. Nilai di luar rentang wajar (setting `bbm_kmpl_min`, `bbm_kmpl_max`) ditandai **anomali** (peringatan, tidak menolak).
- `pengiriman_id` opsional; bila diisi harus satu kendaraan dan satu cabang.
- Input dari sopir (portal) berstatus `menunggu_verifikasi` dan **tidak dihitung** di laporan, km/L, maupun jurnal sebelum diverifikasi admin cabang (A18).

### 6.5 Servis & suku cadang

- Servis `dikerjakan` -> kendaraan `servis` (tidak bisa dijadwalkan). Servis `selesai` -> kendaraan `tersedia` bila tidak ada servis `dikerjakan` lain. Kendaraan yang sedang `bertugas` tidak boleh masuk servis (tolak).
- Pemakaian suku cadang memakai `StokSukuCadangService::keluar()` saat servis diselesaikan: transaksi, `lockForUpdate`, **stok tidak boleh negatif** (tolak dengan pesan jelas), mencatat `mutasi_suku_cadangs`.
- `total_biaya = biaya_jasa + sum(qty x harga_satuan)` dihitung server. `harga_satuan` di baris servis disalin dari harga saat itu (snapshot), tidak membaca ulang dari master.
- Pengingat servis berikutnya: berdasarkan `servis_berikutnya_km` (dibanding odometer terakhir) dan `servis_berikutnya_tanggal`.
- **Semua** perubahan stok hanya lewat `StokSukuCadangService` (`masuk`, `keluar`, `penyesuaian`). Dilarang mengubah `stok_suku_cadangs.qty` dari controller. Stok di bawah `stok_minimum` tampil sebagai peringatan.

### 6.6 Definisi laporan operasional (harus konsisten di dashboard dan laporan)

Hanya data berstatus `terverifikasi` yang dihitung. Laporan keuangan formal (laba rugi, neraca, arus kas, dan lain-lain) didefinisikan di `docs/MODUL-KEUANGAN.md`; **biaya armada operasional di bawah ini tidak termasuk gaji** (A11).

- **Biaya armada** per kendaraan/cabang/periode = BBM + servis (jasa + suku cadang, hanya servis `selesai`, menurut `tanggal_selesai`) + biaya operasional lain.
- **KM tempuh** = jumlah `odometer_akhir - odometer_awal` pengiriman `selesai` dalam periode.
- **Biaya per km** = biaya armada / KM tempuh (tampilkan "-" bila KM tempuh 0).
- **Efisiensi BBM** = km/L menurut 6.4.
- **Jumlah pengiriman** = pengiriman `selesai` dalam periode (menurut `waktu_tiba`).
- **Tingkat keberhasilan** = pesanan `terkirim` / (`terkirim` + `gagal_kirim`) dalam periode.
- **Ketepatan waktu** = pengiriman `selesai` dengan `waktu_tiba <= estimasi_tiba` / semua pengiriman `selesai` yang punya estimasi.
- **Utilisasi** = jumlah hari kendaraan punya pengiriman `berjalan`/`selesai` / jumlah hari dalam periode.
- Semua laporan: filter periode, cabang (pusat), kendaraan; ekspor Excel/CSV; angka dihitung di service (bukan di Blade) dan diuji dengan data contoh.

### 6.7 Penomoran

`NomorDokumenService::buat(cabangId, jenis, periode)` di dalam transaksi dengan `lockForUpdate`. Contoh: pengiriman `PGR-{KODE_CABANG}-YYYYMM-0001`, kode master `KND-{KODE_CABANG}-0001`.

---

## 7. Keamanan & Perlindungan Data

- **Form Request** untuk semua input; `$fillable` ketat; jangan `create($request->all())`.
- **Otorisasi berlapis:** middleware peran + Policy + global scope cabang. Tulis test yang mencoba mengakses data cabang lain.
- **Data pribadi:** `no_sim`, `no_hp_pengirim`, `no_hp_penerima` memakai cast `encrypted`; di daftar ditampilkan disamarkan (mis. `••••••1234`). Konsekuensi: tidak bisa dicari berdasarkan nomor telepon (pencarian lewat kode resi/nama).
- **Kata sandi & sesi:** minimal 8 karakter; rate limit login; regenerasi sesi saat login; `SESSION_SECURE_COOKIE` di produksi; pengguna nonaktif ditolak.
- **Riwayat perubahan:** `spatie/laravel-activitylog` pada Kendaraan, Karyawan, Pesanan, Pengiriman, BbmLog, ServisKendaraan, SukuCadang, StokSukuCadang, BiayaOperasional, User (kecuali `password`). Simpan hanya atribut yang berubah, sertakan `cabang_id`. UI **hanya baca**; tidak ada tombol ubah/hapus.
- **Integritas:** `DB::transaction()` untuk operasi multi-tabel; foreign key; soft delete; data yang punya relasi tidak boleh dihapus (nonaktifkan). Cegah kirim ganda: tombol dinonaktifkan saat submit + pemeriksaan di server.
- **Pelacakan publik (Fase 9):** `GET /lacak/{kode_resi}` tanpa login; rate limit ketat; hanya menampilkan timeline status, kota tujuan, dan waktu; **tanpa nama, alamat lengkap, atau nomor telepon**; respons sama untuk resi tidak ditemukan (cegah penebakan); dapat dimatikan lewat setting.
- **Portal sopir:** akses berbasis `karyawan_id` (Policy per objek), nomor telepon penerima penuh hanya pada pengiriman aktif hari itu, kata sandi awal wajib diganti, akun ikut nonaktif bersama karyawan.
- **Berkas unggahan:** disk privat, validasi MIME + ekstensi + ukuran, nama acak, akses lewat rute terotorisasi/URL bertanda tangan.
- **Keuangan dan gaji:** jurnal terposting tidak dapat diubah (koreksi lewat jurnal balik), periode tutup terkunci, pemisahan tugas pembuat/penyetuju (maker-checker), `no_rekening` dan `npwp` terenkripsi, slip hanya untuk pemiliknya, ekspor keuangan tercatat.
- **Lokasi (GPS):** hanya selama pengiriman berjalan dan dengan persetujuan sopir; hanya admin yang melihat; retensi terbatas; rate limit dan validasi ping; tidak tampil di halaman publik.
- **Umum:** cegah SQL injection (Eloquent/binding; larang `DB::raw` dengan input pengguna); `APP_DEBUG=false` di produksi; HTTPS; `.env` tidak boleh diakses web; header keamanan dasar; backup database terjadwal; jangan menaruh rahasia di repositori (`.env.example` saja).

---

## 8. Desain Antarmuka

- Tailwind + Alpine; komponen Blade dipakai ulang (`x-card`, `x-badge-status`, `x-tabel`, `x-modal-konfirmasi`, `x-form-*`). **Dua layout:** admin (pola komponen Breeze `<x-app-layout>` dengan `{{ $slot }}` agar halaman auth/profil tetap jalan) dan **portal sopir** (`<x-portal-layout>`, mobile-first, navigasi bawah).
- Gaya admin: sidebar kiri terang, header dengan badge peran/cabang, latar abu sangat terang, kartu putih berborder tipis, warna primer biru-teal, badge status berwarna (hijau selesai/tersedia, biru berjalan, oranye menunggu/servis, merah gagal/kedaluwarsa, abu final).
- **Responsif:** sidebar menjadi drawer di layar kecil; tabel besar menjadi kartu bertumpuk atau digulir horizontal di dalam wadahnya (halaman tidak boleh menggulir samping); target sentuh >= 40px; formulir satu kolom di ponsel. Portal sopir: tombol besar, satu tangan, kontras tinggi, dapat dipasang di layar utama.
- Setiap daftar: pencarian, filter, urut, paginasi 10-15 baris, keadaan kosong yang informatif, notifikasi sukses/gagal, konfirmasi sebelum hapus/batal. Laporan: filter periode/cabang, ekspor.
- Halaman/menu yang belum dikerjakan tampil sebagai placeholder yang jelas atau disembunyikan; jangan menampilkan menu yang menuju halaman rusak.

### Menu sidebar admin (urutan persis; grup tampil sesuai izin)

```
UTAMA          Dashboard Operasional
OPERASIONAL    Pesanan · Penjadwalan Pengiriman · Pelacakan Status · Peta Armada
ARMADA         Kendaraan · Sopir & Karyawan · Absensi · Pencatatan BBM · Servis & Perawatan · Verifikasi Lapangan
INVENTORI      Suku Cadang (stok & mutasi)
PENAGIHAN      Pelanggan · Tarif · Invoice · Pembayaran · Piutang
KEUANGAN †     Jurnal & Buku Besar · Hutang Usaha · Aset Tetap · Penggajian · Anggaran · Periode Akuntansi · Laporan Keuangan
LAPORAN        Biaya Armada · Performa Armada
KOMUNIKASI     Pengumuman
PENGATURAN     Data Cabang* · Akun & Akses* · Riwayat Perubahan · Pengaturan*
(* hanya admin_pusat · † hanya admin_pusat dan keuangan)
```

### Menu portal sopir (navigasi bawah)

`Beranda · Jadwal · Pengumuman · Slip Gaji · Profil` (rincian layar di `docs/MODUL-SOPIR-GPS.md`).

### Dashboard Operasional

Kartu: Pesanan Hari Ini (per status), Pengiriman Berjalan, Status Armada (tersedia/bertugas/servis), Dokumen Kendaraan dan SIM Segera Kedaluwarsa (<= 30 hari), Suku Cadang di Bawah Minimum, Antrean Verifikasi Lapangan, Biaya Bulan Ini; untuk `keuangan`/`admin_pusat` ditambah Piutang Jatuh Tempo dan Kas & Bank. Grafik: pengiriman 7 hari terakhir dan biaya per kategori. Tabel: pengiriman berjalan (sopir, kendaraan, estimasi tiba). `admin_pusat` punya dropdown filter cabang.

---

## 9. Fase Pengerjaan (berurutan; tiap fase punya Kriteria Selesai = KS)

Fase yang memakai modul: **baca bagian yang disebut di modul itu saja** (`docs/MODUL-SOPIR-GPS.md`, `docs/MODUL-KEUANGAN.md`). Migration tabel modul dibuat pada fasenya.

### RILIS 1 — Operasional Inti

#### Fase 0 — Persiapan
Proyek Laravel 11 baru, `.env` (MySQL Laragon, timezone, locale `id`), Breeze (Blade), spatie permission + activitylog, Tailwind/Alpine/Vite, paket ekspor/PDF, git, folder `docs/` dengan `PROGRESS.md` dan `ASUMSI.md` (salin bagian 2), bahasa validasi Indonesia.
**KS:** halaman login tampil; `npm run build` sukses; `php artisan test` berjalan.

#### Fase 1 — Database Inti, Peran, Keamanan Dasar
Migration tabel bagian 4 (tanpa tabel modul), enum PHP untuk status, model + relasi, trait `BelongsToCabang`, `users` dengan `username`/`karyawan_id`/`wajib_ganti_password`, login memakai username **atau** email, 4 peran + izin, seeder (termasuk `keuangan` dan 2 akun `sopir` contoh), `settings` + helper `setting()`, `NomorDokumenService`, konfigurasi activitylog (+ `cabang_id`), middleware pengguna/karyawan nonaktif dan wajib ganti kata sandi.
**KS:** `migrate:fresh --seed` sukses; login semua peran; **test isolasi cabang**; sopir tidak dapat mengakses rute admin; nomor dokumen unik berurutan.

#### Fase 2 — Layout Admin, Kerangka Portal, Dashboard Kerangka
Layout admin (sidebar, header, badge peran) dan menu bagian 8 sesuai izin, layout portal sopir (kerangka + navigasi bawah), halaman menu lain placeholder, dashboard kerangka dengan kartu berisi data nyata sederhana.
**KS:** responsif (desktop dan lebar ponsel); tidak ada 419/500 saat login, logout, pindah halaman, ganti akun; menu sesuai peran; sopir diarahkan ke portal.

#### Fase 3 — Data Master
CRUD Data Cabang, Akun & Akses (buat/nonaktifkan/reset kata sandi; buat akun sopir dari karyawan), Karyawan (jabatan, SIM terenkripsi), Kendaraan (dokumen berlaku, status), **Pelanggan, Pemasok, Tarif** (struktur di `docs/MODUL-KEUANGAN.md` bagian 2). Kode otomatis; peringatan dokumen <= 30 hari; soft delete; data berelasi tidak boleh dihapus.
**KS:** validasi lengkap (plat unik, tanggal masuk akal, kapasitas > 0); SIM/NPWP tersimpan terenkripsi (dicek langsung di DB); test otorisasi per peran; pencarian dan filter berfungsi.

#### Fase 4 — Pesanan & Pelacakan Status
CRUD Pesanan (status `draf`) dengan `pelanggan_id` opsional dan **usulan tarif** dari master (dapat ditimpa dengan alasan), `PesananStatusService` (tabel 6.1), `kode_resi`, detail dengan **timeline status**, aksi ubah status yang hanya menampilkan transisi valid, pembatalan dengan alasan.
**KS:** test setiap transisi valid dan invalid; riwayat status tidak bisa diedit; `terkirim` mewajibkan nama penerima + waktu; kode resi unik dan acak; tarif usulan benar dan penimpaan tercatat.

#### Fase 5 — Penjadwalan Multi-Rit, Penugasan Sopir, Eksekusi Pengiriman
`PenjadwalanService` (6.2, termasuk jendela waktu dan `rit_ke`) + `PengirimanService` (6.3), form penjadwalan (pilih pesanan, total berat tampil langsung, dihitung ulang di server), daftar **semua** alasan penolakan, mulai/selesai/batal pengiriman, papan pengiriman per hari.
**KS:** test: tumpang tindih jendela waktu ditolak dan dua rit berurutan diterima; jeda antar rit dihormati; kelebihan muatan ditolak; SIM/STNK/KIR/pajak kedaluwarsa ditolak; override pusat butuh alasan dan tercatat; pengiriman tidak bisa selesai bila ada pesanan belum final; odometer tidak boleh mundur; dua permintaan bersamaan tidak menghasilkan jadwal ganda; peringatan jam kerja muncul.

#### Fase 6 — BBM & Biaya Operasional
CRUD BBM (`total` dihitung server, validasi odometer, km/L, penanda anomali), CRUD Biaya Operasional, kolom `sumber`/`status_verifikasi` (input admin langsung `terverifikasi`), ringkasan per kendaraan.
**KS:** test km/L (tangki penuh berurutan dan sebagian); odometer mundur ditolak; anomali tertandai; hanya data `terverifikasi` masuk perhitungan.

#### Fase 7 — Servis & Suku Cadang
Master suku cadang, stok per cabang, `StokSukuCadangService`, penerimaan stok, servis dengan pemakaian suku cadang, status kendaraan mengikuti servis, pengingat servis berikutnya, peringatan stok minimum.
**KS:** stok tidak bisa negatif; pemakaian mencatat mutasi; total biaya servis benar; kendaraan `bertugas` tidak bisa masuk servis; kendaraan `servis` tidak bisa dijadwalkan.

#### Fase 8 — Portal Sopir  (rincian: `docs/MODUL-SOPIR-GPS.md`, Fase 8)
Akun/login sopir, Beranda, Jadwal, Detail Pengiriman, aksi lapangan dengan bukti foto, Catat BBM/Biaya Jalan (menunggu verifikasi), halaman Verifikasi untuk admin cabang, Profil.

#### Fase 9 — Pengumuman & Absensi  (rincian: `docs/MODUL-SOPIR-GPS.md`, Fase 9)

**Kriteria Rilis 1:** semua KS Fase 0-9 lulus, `php artisan test` hijau, panduan singkat penggunaan di `docs/PROGRESS.md`. Uji coba dengan pengguna nyata sebelum lanjut.

### RILIS 2 — Keuangan & Penggajian  (rincian lengkap dan KS: `docs/MODUL-KEUANGAN.md`, bagian 12)

- **Fase 10** Fondasi Akuntansi (bagan akun, periode, jurnal, buku besar, neraca saldo)
- **Fase 11** Penagihan & Piutang (tarif, invoice, pembayaran, aging)
- **Fase 12** Hutang Usaha, Aset Tetap, Jurnal Otomatis Operasional
- **Fase 13** Penggajian (komponen, kasbon, slip, publikasi ke portal sopir)
- **Fase 14** Laporan Keuangan Profesional & Penutupan Periode

**Kriteria Rilis 2:** semua KS Fase 10-14 lulus; halaman Rekonsiliasi hijau pada data uji; akuntan/pemilik meninjau bagan akun dan contoh laporan sebelum dipakai.

### RILIS 3 — Pelacakan, Dasbor, Pengerasan

#### Fase 15 — GPS  (rincian: `docs/MODUL-SOPIR-GPS.md`, bagian 9-10)

#### Fase 16 — Dashboard Nyata & Laporan Operasional
Dashboard bagian 8 dengan data nyata + grafik, `BiayaArmadaService` dan `KinerjaArmadaService` (definisi 6.6), halaman Laporan Biaya Armada dan Performa Armada dengan filter dan ekspor.
**KS:** angka cocok dengan dataset uji yang dihitung manual; dashboard dan laporan memakai definisi yang sama; filter cabang pusat berfungsi; angka operasional konsisten dengan laporan keuangan (bagian yang beririsan).

#### Fase 17 — Riwayat Perubahan, Pelacakan Publik, Pengerasan
Halaman Riwayat Perubahan (baca-saja, filter model/pengguna/tanggal/cabang), halaman pelacakan publik (bagian 7), audit keamanan seluruh route dan Policy, cek N+1 dan indeks, rate limit, header keamanan, uji beban ringan pada laporan.
**KS:** tidak ada route tanpa middleware/Policy yang semestinya (dicek lewat `route:list`); halaman publik tidak membocorkan data pribadi maupun lokasi (test); semua test lulus.

#### Fase 18 — Dokumentasi & Deploy
- `docs/TEKNIS.md`: arsitektur, struktur folder, ERD (Mermaid), diagram status pesanan/pengiriman (Mermaid), alur jurnal otomatis, penjelasan tiap service, setup lokal, `.env`, seeding, menjalankan test, konvensi, tautan ke `ASUMSI.md`, panduan deploy.
- `docs/PANDUAN-PENGGUNA.md`: **per peran** (admin pusat, keuangan, admin cabang, sopir); langkah membuat pesanan, menjadwalkan, verifikasi lapangan, mencatat BBM/servis, menerbitkan invoice dan mencatat pembayaran, menjalankan penggajian, menutup periode, membaca laporan keuangan; arti setiap status; pemecahan masalah (penjadwalan ditolak, stok kurang, error 419, lokasi tidak terkirim).
- Deploy: `APP_ENV=production`, `APP_DEBUG=false`, **HTTPS**, `key:generate`, `migrate --force`, `storage:link`, `config/route/view:cache`, izin `storage` dan `bootstrap/cache`, disk privat untuk unggahan, **cron `php artisan schedule:run` tiap menit** (retensi lokasi, pengingat dokumen), backup database dan berkas terjadwal, ganti semua kata sandi seeder. cPanel: document root ke `public/` (atau sesuaikan `index.php`), PHP >= 8.2 dengan ekstensi umum. VPS: Nginx/Apache + PHP-FPM + supervisor bila memakai antrean.
**KS:** orang baru dapat menjalankan proyek dari nol hanya dengan `TEKNIS.md`; panduan mencakup semua alur utama; aplikasi berjalan di produksi dengan debug mati dan HTTPS.

---

## 10. Pengujian Wajib (ringkasan)

Isolasi cabang; otorisasi per peran; semua transisi status pesanan; aturan penjadwalan (6.2) satu per satu; eksekusi pengiriman (6.3); perhitungan BBM dan km/L; stok suku cadang tidak negatif dan transaksi atomik; perhitungan biaya dan performa (6.6) dengan dataset kecil; enkripsi kolom sensitif; halaman publik tidak membocorkan data pribadi; pengguna nonaktif tidak bisa login; kode resi unik; pembatasan data sopir; tumpang tindih jendela waktu multi-rit; verifikasi input lapangan; berkas privat; serta seluruh pengujian di `docs/MODUL-KEUANGAN.md` (bagian 11) dan `docs/MODUL-SOPIR-GPS.md` (bagian 11).

---

## 11. Standar Kode

PSR-12; controller tipis, logika di `Services`; enum untuk status; tidak ada perhitungan angka di Blade; tidak ada query di Blade; eager loading untuk mencegah N+1; nama dan komentar konsisten dengan dokumen ini; setiap keputusan baru dicatat di `docs/ASUMSI.md` atau `docs/PROGRESS.md`.

---

## 12. Pertanyaan Terbuka (jangan diasumsikan diam-diam)

1. Berapa batas jam kerja/istirahat sopir dan jeda antar rit yang berlaku di perusahaan (A5)? Apakah pelanggaran sebaiknya ditolak, bukan hanya peringatan?
2. Komponen gaji nyata (gaji pokok, uang makan, tarif ritase, lembur) dan apakah PPh 21/BPJS dikelola manual atau otomatis (A17)?
3. Perlakuan PPN dan PPh atas jasa angkutan yang berlaku bagi usaha ini: **konfirmasi akuntan/konsultan pajak** (A16).
4. Bagan akun dan kebijakan akuntansi final: disetujui akuntan? Perlu anggaran tahunan (Anggaran vs Realisasi)?
5. Siapa yang memverifikasi input BBM/biaya dari sopir dan berapa lama batas waktunya (A18)?
6. GPS: cukup ponsel sopir (Opsi A), atau perlu Traccar/perangkat keras (Opsi B/C)? Berapa jumlah kendaraan dan anggarannya?
7. Kebijakan privasi lokasi: jam pelacakan, retensi (default 90 hari), dan persetujuan tertulis sopir (A21).
8. Kelebihan bayar pelanggan: ditolak (default) atau dicatat sebagai uang muka?
9. Perlukah akun/portal untuk kernet dan mekanik?
10. Hosting produksi (cPanel atau VPS), HTTPS, dan kebutuhan backup berkas/foto.

---

## 13. Template Permintaan per Fase (untuk pengguna)

```
Kerjakan FASE [N] dari @FLEETFLOW-GUIDE.md. Baca bagian 0-3 dan bagian yang relevan saja.
Bila fase memakai modul, baca juga bagian yang disebut di @docs/MODUL-KEUANGAN.md atau @docs/MODUL-SOPIR-GPS.md.
Audit dulu apa yang sudah ada, buat rencana singkat, lalu tunggu persetujuan saya.
Setelah disetujui: kerjakan, jalankan test dan build, perbarui docs/PROGRESS.md,
siapkan pesan commit, dan laporkan ringkas (maks. 10 baris).
```
