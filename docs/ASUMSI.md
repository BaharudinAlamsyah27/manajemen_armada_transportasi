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
