# MODUL KEUANGAN — Akuntansi, Penagihan, Hutang, Aset, Penggajian, Laporan

Rujukan: `@FLEETFLOW-GUIDE.md` (aturan kerja bagian 0, asumsi A9, A11, A16-A19, peran bagian 5). Modul ini dikerjakan pada **Fase 10-14** (Rilis 2), **setelah** Rilis 1 (operasional) lulus semua kriteria selesai.

> **Catatan profesional:** struktur akun dan laporan disusun mengacu pada praktik akuntansi entitas kecil di Indonesia (SAK EMKM). Perlakuan pajak (PPN, PPh) dibuat **dapat dikonfigurasi** dan **tidak diasumsikan**; daftar akun, kebijakan akuntansi, dan tarif pajak wajib dikonfirmasi akuntan/konsultan pajak pemilik usaha sebelum dipakai untuk pelaporan resmi. Agent dilarang menetapkan tarif atau perlakuan pajak sendiri.

---

## 1. Prinsip Akuntansi (tidak boleh dilanggar)

1. **Pembukuan berpasangan (double-entry):** setiap jurnal `total debit = total kredit`. Validasi di server **dan** pemeriksaan di database (sebelum posting).
2. **Dasar akrual**, mata uang **IDR**, `decimal(15,2)`. Tidak ada float.
3. **Jurnal terposting tidak boleh diedit atau dihapus.** Koreksi hanya lewat **jurnal balik** (`membalik_jurnal_id`) lalu jurnal baru.
4. **Periode terkunci:** posting ke periode berstatus `tutup` ditolak.
5. **Jurnal otomatis bersifat idempoten:** unik per (`sumber_type`, `sumber_id`, `peristiwa`); memproses ulang tidak menggandakan jurnal.
6. **Subledger = akun kontrol:** total piutang per invoice = saldo akun Piutang Usaha; total stok x nilai = Persediaan; total tagihan pemasok = Hutang Usaha. Selisih ditandai di halaman Rekonsiliasi.
7. **Dimensi analitik** pada `jurnal_details`: `cabang_id` (di header), `kendaraan_id`, `pengiriman_id`, `pelanggan_id` (nullable) untuk laporan profitabilitas.
8. Semua nominal **dihitung di server** oleh service; tidak ada perhitungan di Blade atau dari input browser.
9. Data keuangan tidak dihapus permanen (soft delete atau pembatalan berstatus + jurnal balik).

---

## 2. Skema

| Tabel | Kolom penting |
|---|---|
| `akuns` | kode (unique), nama, tipe (`aset`,`liabilitas`,`ekuitas`,`pendapatan`,`beban`), saldo_normal (`debit`,`kredit`), induk_id, kategori_arus_kas (`operasi`,`investasi`,`pendanaan`, null), is_kas (bool), aktif, sistem (bool; akun sistem tidak boleh dihapus) |
| `periode_akuntansis` | tahun, bulan, status (`buka`,`tutup`), ditutup_oleh, ditutup_pada, alasan_buka_kembali. Unique (tahun, bulan) |
| `jurnals` | cabang_id, no_jurnal, tanggal, periode_id, keterangan, jenis (`manual`,`otomatis`,`penutup`,`balik`), peristiwa (nullable), sumber_type, sumber_id, status (`draf`,`posted`), membalik_jurnal_id (nullable), dibuat_oleh, disetujui_oleh, diposting_pada. Unique (sumber_type, sumber_id, peristiwa) untuk jurnal otomatis |
| `jurnal_details` | jurnal_id, akun_id, debit, kredit, keterangan, kendaraan_id, pengiriman_id, pelanggan_id |
| `pelanggans` | kode, nama, **npwp (encrypted, nullable)**, alamat, kontak, no_hp, termin_hari, aktif (master global) |
| `tarifs` | kota_asal, kota_tujuan, jenis_kendaraan (nullable), harga_per_kg, tarif_minimum, berlaku_sejak, aktif |
| `invoices` | cabang_id, no_invoice, pelanggan_id, tanggal, jatuh_tempo, subtotal, ppn_persen, ppn_nominal, total, terbayar, status (`draf`,`terbit`,`sebagian`,`lunas`,`batal`), catatan, dibuat_oleh |
| `invoice_items` | invoice_id, pesanan_id, deskripsi, qty, harga, jumlah |
| `pembayarans` | cabang_id, no_pembayaran, pelanggan_id, tanggal, metode, akun_kas_id, jumlah_diterima, potongan_pajak (default 0), referensi, dicatat_oleh, status (`aktif`,`batal`) |
| `pembayaran_alokasis` | pembayaran_id, invoice_id, jumlah_alokasi, potongan_pajak |
| `pemasoks` | kode, nama, jenis (`spbu`,`bengkel`,`supplier`,`lainnya`), kontak, termin_hari, aktif |
| `tagihan_pemasoks` | cabang_id, no_tagihan, pemasok_id, tanggal, jatuh_tempo, jumlah, terbayar, status (`terbit`,`sebagian`,`lunas`,`batal`), sumber_type, sumber_id |
| `pembayaran_pemasoks` | cabang_id, tagihan_id, tanggal, jumlah, akun_kas_id, referensi |
| `aset_tetaps` | cabang_id, kendaraan_id (nullable), nama, tanggal_perolehan, harga_perolehan, nilai_residu, umur_bulan, metode (`garis_lurus`), akumulasi_penyusutan, status (`aktif`,`dilepas`) |
| `penyusutans` | aset_id, periode_id, jumlah, jurnal_id. Unique (aset_id, periode_id) |
| `anggarans` | cabang_id, tahun, bulan, akun_id, jumlah. Unique (cabang_id, tahun, bulan, akun_id) |
| `komponen_gajis` | kode, nama, jenis (`pendapatan`,`potongan`), tipe_hitung (`tetap`,`per_hari_hadir`,`per_ritase`,`manual`), nominal_default, akun_id, aktif |
| `profil_gajis` | karyawan_id, komponen_id, nominal, berlaku_sejak. **`karyawans.no_rekening` (encrypted)** ditambahkan |
| `periode_gajis` | cabang_id, tahun, bulan, status (`draf`,`dihitung`,`disetujui`,`dibayar`,`dipublikasikan`), dibuat_oleh, disetujui_oleh, tanggal_bayar, akun_kas_id. Unique (cabang_id, tahun, bulan) |
| `slip_gajis` | periode_gaji_id, karyawan_id, no_slip, total_pendapatan, total_potongan, gaji_bersih, hari_hadir, jumlah_ritase, catatan |
| `slip_gaji_items` | slip_id, komponen_id, keterangan, jenis, jumlah |
| `kasbons` | cabang_id, karyawan_id, tanggal, jumlah, cicilan_per_bulan, sisa, status (`aktif`,`lunas`), jurnal_id |
| `pengaturan_keuangan` (pakai `settings`) | ppn_persen (default 0), akun_default_kas, akun_default_bank, hari_jatuh_tempo_default, wajib_persetujuan_berbeda_user (default true), maks_gaji_negatif=false |

Penomoran lewat `NomorDokumenService`: `INV-{KODE}-YYYYMM-0001`, `PAY-...`, `JU-...`, `SLP-...`, `TGH-...`.

---

## 3. Bagan Akun Bawaan (dapat diubah; akun `sistem` tidak boleh dihapus)

| Kode | Nama | Tipe | Arus kas |
|---|---|---|---|
| 1-1100 | Kas | aset (kas) | - |
| 1-1200 | Bank | aset (kas) | - |
| 1-1300 | Piutang Usaha | aset | operasi |
| 1-1400 | Persediaan Suku Cadang | aset | operasi |
| 1-1500 | Piutang Karyawan (Kasbon) | aset | operasi |
| 1-1650 | Pajak Dibayar Dimuka | aset | operasi |
| 1-1700 | PPN Masukan | aset | operasi |
| 1-2100 | Kendaraan | aset tetap | investasi |
| 1-2190 | Akumulasi Penyusutan Kendaraan | aset tetap (kontra) | investasi |
| 2-1100 | Hutang Usaha | liabilitas | operasi |
| 2-1200 | Hutang Gaji | liabilitas | operasi |
| 2-1300 | PPN Keluaran | liabilitas | operasi |
| 2-1400 | Hutang Lain-lain | liabilitas | operasi |
| 3-1100 | Modal Disetor | ekuitas | pendanaan |
| 3-2100 | Laba Ditahan | ekuitas | - |
| 4-1100 | Pendapatan Jasa Angkut | pendapatan | operasi |
| 4-1900 | Pendapatan Lain-lain | pendapatan | operasi |
| 5-1100 | Beban BBM | beban pokok jasa | operasi |
| 5-1200 | Beban Perawatan & Servis | beban pokok jasa | operasi |
| 5-1300 | Beban Tol & Parkir | beban pokok jasa | operasi |
| 5-1400 | Beban Denda & Lain Operasional | beban pokok jasa | operasi |
| 5-1500 | Beban Pajak & Perizinan Kendaraan | beban pokok jasa | operasi |
| 5-1600 | Beban Penyusutan Kendaraan | beban pokok jasa | - |
| 6-1100 | Beban Gaji & Tunjangan | beban usaha | operasi |
| 6-1200 | Beban Uang Jalan/Ritase | beban usaha | operasi |
| 6-2100 | Beban Sewa & Utilitas | beban usaha | operasi |
| 6-9900 | Beban Lain-lain | beban usaha | operasi |

Laba tahun berjalan **dihitung** (bukan akun tersimpan) dari pendapatan dikurangi beban sampai tanggal laporan.

---

## 4. Jurnal Otomatis (`JurnalOtomatisService`)

Dipanggil **di dalam transaksi yang sama** dengan peristiwa bisnisnya. Akun kas/bank mengikuti pilihan pengguna atau setting default.

| Peristiwa | Debit | Kredit |
|---|---|---|
| Invoice terbit | Piutang Usaha (total) | Pendapatan Jasa Angkut (subtotal); PPN Keluaran (ppn) |
| Invoice batal | jurnal balik dari jurnal terbit | |
| Pembayaran pelanggan | Kas/Bank (diterima); Pajak Dibayar Dimuka (potongan pajak) | Piutang Usaha (alokasi) |
| Pembayaran batal | jurnal balik | |
| Penerimaan stok (tunai) | Persediaan | Kas/Bank |
| Penerimaan stok (tempo) | Persediaan | Hutang Usaha (+ tagihan pemasok) |
| Servis selesai | Beban Perawatan (jasa + suku cadang terpakai) | Kas/Bank atau Hutang Usaha (jasa); Persediaan (suku cadang terpakai, nilai dari harga stok) |
| BBM terverifikasi | Beban BBM | Kas/Bank atau Hutang Usaha (sesuai metode bayar) |
| Biaya operasional terverifikasi | Beban sesuai jenis (tol/parkir/denda/lain) | Kas/Bank |
| Pembayaran tagihan pemasok | Hutang Usaha | Kas/Bank |
| Penyusutan bulanan | Beban Penyusutan Kendaraan | Akumulasi Penyusutan Kendaraan |
| Kasbon diberikan | Piutang Karyawan | Kas/Bank |
| Penggajian disetujui | Beban Gaji & Tunjangan; Beban Uang Jalan/Ritase | Hutang Gaji (bersih); Piutang Karyawan (potongan kasbon); Hutang Lain-lain (potongan lain) |
| Gaji dibayar | Hutang Gaji | Kas/Bank |

Aturan: dimensi `kendaraan_id`, `pengiriman_id`, `pelanggan_id` diisi bila tersedia. Bila periode tutup, peristiwa **ditolak dengan pesan jelas** (jangan diam-diam pindah periode). Sediakan perintah artisan `jurnal:backfill` (idempoten) untuk data historis yang dibuat sebelum modul aktif.

---

## 5. Penagihan & Piutang

- **Tarif:** tarif pesanan diusulkan dari master `tarifs` (per kota tujuan x berat, dengan tarif minimum); pengguna boleh menimpa dengan alasan tercatat. Tanpa tarif yang cocok, input manual.
- **Invoice:** dibuat dari pesanan `terkirim` milik **satu pelanggan** (banyak pesanan per invoice). Satu pesanan hanya masuk satu invoice aktif. PPN memakai `ppn_persen` dari setting, dapat diganti per invoice; **default 0** dan tampil transparan. Setelah `terbit`, invoice **tidak dapat diedit** (batal + buat baru).
- **Jatuh tempo:** `tanggal + termin_hari` pelanggan (default setting).
- **Pembayaran:** satu pembayaran dapat dialokasikan ke banyak invoice pelanggan yang sama. Alokasi tidak boleh melebihi sisa invoice; kelebihan bayar **ditolak** di MVP (dicatat sebagai pertanyaan terbuka). `potongan_pajak` (mis. dari bukti potong pelanggan) diinput manual dan menjadi Pajak Dibayar Dimuka. Status invoice otomatis `sebagian`/`lunas`.
- **Aging piutang:** kelompok belum jatuh tempo, 1-30, 31-60, 61-90, >90 hari (terhadap tanggal laporan), per pelanggan dan per cabang; total harus sama dengan saldo akun 1-1300.
- **Cetak:** invoice dan kwitansi PDF (kop cabang, nomor, terbilang rupiah, blok tanda tangan).

---

## 6. Hutang Usaha & Aset Tetap

- **Hutang usaha:** tagihan pemasok (dari penerimaan stok/servis/BBM tempo atau input manual), pembayaran parsial diperbolehkan, aging hutang, status otomatis.
- **Aset tetap:** kendaraan didaftarkan sebagai aset (harga perolehan, nilai residu, umur bulan). **Penyusutan garis lurus bulanan** = `(harga_perolehan - nilai_residu) / umur_bulan`, mulai bulan **setelah** perolehan (dapat diubah lewat setting), berhenti saat nilai buku = residu; bulan terakhir menyerap selisih pembulatan. Proses penyusutan bulanan idempoten (unique aset+periode) dan menghasilkan jurnal.

---

## 7. Penggajian (`PayrollService`)

**Komponen pendapatan:** gaji pokok (tetap), tunjangan tetap, uang makan (`per_hari_hadir`: tarif x hari hadir dari `absensis` berstatus hadir), uang jalan/ritase (`per_ritase`: tarif x jumlah pengiriman `selesai` milik sopir pada periode), lembur dan bonus (manual). **Komponen potongan:** cicilan kasbon (otomatis dari `kasbons` aktif, tidak melebihi sisa), denda, potongan lain (manual). PPh 21 dan BPJS **tidak dihitung otomatis** di MVP; bila dibutuhkan diinput sebagai komponen manual (otomatisasi menunggu keputusan pemilik dan akuntan).

`gaji_bersih = total_pendapatan - total_potongan`; nilai negatif ditolak (validasi + pesan).

**Alur status:** `draf` -> `dihitung` (sistem menghitung slip semua karyawan cabang) -> `disetujui` (pemeriksa **berbeda** dari pembuat bila setting aktif; jurnal beban dibuat; slip dibekukan) -> `dibayar` (tanggal, akun kas; jurnal pembayaran) -> `dipublikasikan` (slip terlihat di portal sopir). Koreksi setelah `disetujui`: hanya `admin_pusat`/`keuangan` dengan alasan (jurnal balik + hitung ulang), atau penyesuaian di periode berikutnya.

**Slip gaji:** PDF per karyawan (kop, periode, identitas, rincian pendapatan/potongan, gaji bersih, hari hadir, ritase). Nomor rekening tampil disamarkan. Sopir hanya melihat slip miliknya yang berstatus `dipublikasikan`.

---

## 8. Periode & Penutupan

- Periode bulanan dibuat otomatis. **Tutup periode** hanya `keuangan`/`admin_pusat`; prasyarat: tidak ada jurnal `draf`, penyusutan bulan itu selesai, periode gaji bulan itu `dibayar` atau tidak ada, dan **tidak ada** BBM/biaya berstatus `menunggu_verifikasi` pada periode tersebut, serta rekonsiliasi tanpa selisih.
- **Buka kembali** periode: hanya `admin_pusat`, alasan wajib, tercatat di riwayat.
- **Tutup tahun:** `TutupTahunService` membuat jurnal penutup yang memindahkan laba/rugi tahun berjalan ke Laba Ditahan; hanya setelah 12 periode ditutup.

---

## 9. Laporan Keuangan (semua dapat difilter per cabang; pusat/keuangan juga **konsolidasi** semua cabang)

| Laporan | Isi dan definisi |
|---|---|
| **Laba Rugi** | Pendapatan; Beban Pokok Jasa (akun 5-xxxx); **Laba Kotor**; Beban Usaha (6-xxxx); **Laba Usaha**; Pendapatan/Beban Lain; **Laba Bersih**. Kolom: periode ini, periode pembanding, selisih, dan % terhadap pendapatan |
| **Neraca (Laporan Posisi Keuangan)** | Per tanggal: Aset Lancar, Aset Tetap (nilai buku = perolehan - akumulasi), Liabilitas, Ekuitas (termasuk laba tahun berjalan). **Wajib seimbang**: Aset = Liabilitas + Ekuitas; bila tidak, tampil peringatan merah |
| **Arus Kas (metode langsung)** | Dari mutasi akun `is_kas`; setiap baris kas diklasifikasikan lewat `kategori_arus_kas` akun lawan (operasi/investasi/pendanaan; jurnal dengan banyak lawan dialokasikan proporsional). Saldo awal + arus bersih = saldo akhir, dan **harus sama** dengan saldo Kas+Bank di Neraca |
| **Neraca Saldo** | Saldo awal, mutasi debit/kredit, saldo akhir per akun; total debit = total kredit |
| **Buku Besar** | Per akun: saldo awal, daftar jurnal berurutan dengan saldo berjalan, tautan ke jurnal sumber |
| **Jurnal Umum** | Semua jurnal per periode dengan filter jenis/sumber |
| **Aging Piutang & Hutang** | Bagian 5 dan 6; ekspor per pelanggan/pemasok |
| **Profitabilitas** | Per **kendaraan**: pendapatan (invoice_items -> pesanan -> pengiriman `selesai` kendaraan itu) dikurangi BBM, servis, tol/parkir, penyusutan = **margin kontribusi** dan per km; per **pelanggan**: pendapatan, piutang, rata-rata hari bayar; per **pengiriman**: pendapatan vs biaya langsungnya |
| **Anggaran vs Realisasi** | Per akun per bulan/kumulatif: anggaran, realisasi, selisih, % pencapaian |
| **Rekap Penggajian** | Per periode: total pendapatan, potongan, gaji bersih, jumlah karyawan, ritase |
| **Rekonsiliasi** | Cek otomatis: jurnal seimbang; neraca seimbang; piutang subledger = akun 1-1300; persediaan = 1-1400; hutang = 2-1100; kas arus = kas neraca; hasil: hijau/merah dengan rincian selisih |
| **Paket Laporan Keuangan (PDF)** | Sampul (nama usaha/cabang, periode), Laba Rugi, Neraca, Arus Kas, catatan ringkas otomatis (kebijakan akuntansi ringkas, rincian kas, piutang, aset tetap), blok tanda tangan "Disusun oleh / Disetujui oleh" |

**Standar tampilan dan ekspor:** kop dengan nama usaha/cabang dan periode; mata uang tertulis; angka format Indonesia (`1.250.000`); nilai negatif dalam kurung; subtotal/total tebal; nomor halaman; **watermark "DRAF" bila periode belum ditutup**; ekspor **PDF** (dompdf) dan **Excel** (angka sebagai angka, bukan teks). Semua laporan dihitung di service dengan query agregat (bukan memuat semua baris ke memori) dan diuji dengan dataset kecil yang dihitung manual.

---

## 10. Hak Akses & Keamanan Keuangan

- `keuangan` dan `admin_pusat`: seluruh modul. `admin_cabang`: membuat invoice dan mencatat pembayaran pelanggan **cabangnya**, melihat piutang cabangnya; **tidak** dapat mengakses jurnal, buku besar, penggajian, dan laporan keuangan formal (hanya laporan operasional).
- **Pemisahan tugas (maker-checker)** default aktif: jurnal manual dan penggajian disetujui oleh pengguna yang **berbeda** dari pembuatnya.
- Semua tabel keuangan masuk activity log; laporan dan slip tidak menyimpan data sensitif tambahan. `npwp` dan `no_rekening` terenkripsi. Ekspor keuangan tercatat (siapa, kapan, laporan apa).
- Endpoint slip gaji: Policy ketat (pemilik slip atau `keuangan`/`admin_pusat`); tidak ada URL yang bisa ditebak lintas pengguna.

---

## 11. Pengujian Wajib

Jurnal selalu seimbang (uji dengan banyak kombinasi); jurnal terposting tidak bisa diedit/dihapus; periode tutup menolak posting; idempotensi jurnal otomatis; jurnal balik menetralkan saldo; PPN dan pembulatan; alokasi pembayaran tidak melebihi sisa invoice; aging per kelompok; penyusutan (termasuk bulan terakhir); rumus gaji dengan kasus hari hadir/ritase/kasbon; slip hanya dapat dilihat pemiliknya dan hanya setelah `dipublikasikan`; maker-checker; Neraca seimbang dan Arus Kas sama dengan saldo kas; subledger = akun kontrol; laporan konsolidasi = jumlah per cabang; `admin_cabang` ditolak (403) di rute jurnal/penggajian/laporan keuangan.

---

## 12. Fase Rilis 2 (kriteria selesai = KS)

### Fase 10 — Fondasi Akuntansi
Migration `akuns`, `periode_akuntansis`, `jurnals`, `jurnal_details`; seeder bagan akun (bagian 3) dan periode; `JurnalService` (buat, validasi seimbang, posting, balik); UI jurnal manual (draf -> posting, maker-checker); Buku Besar, Neraca Saldo, Jurnal Umum; Daftar Akun (ubah nama/aktifkan; akun sistem terlindungi).
**KS:** test prinsip bagian 1; jurnal tidak seimbang ditolak; jurnal terposting tidak bisa diubah; periode tutup menolak posting.

### Fase 11 — Penagihan & Piutang
Master `pelanggans` dan `tarifs` (bila belum ada di Fase 3), invoice dari pesanan `terkirim`, PPN configurable, jurnal otomatis terbit, pembayaran + alokasi + potongan pajak, aging piutang, cetak invoice/kwitansi PDF, pembatalan dengan jurnal balik.
**KS:** test bagian 5; piutang subledger = akun 1-1300; satu pesanan tidak masuk dua invoice aktif; kelebihan alokasi ditolak.

### Fase 12 — Hutang Usaha, Aset Tetap, Jurnal Otomatis Operasional
Master `pemasoks`, tagihan dan pembayaran pemasok, jurnal otomatis BBM/servis/biaya/penerimaan stok (bagian 4), aset tetap kendaraan + penyusutan bulanan, `jurnal:backfill`.
**KS:** memverifikasi BBM/biaya (Fase 6 dan 8) otomatis menghasilkan jurnal tepat sekali; penyusutan idempoten; persediaan subledger = akun 1-1400; hutang subledger = 2-1100.

### Fase 13 — Penggajian
Komponen gaji, profil gaji, kasbon, periode gaji dan `PayrollService`, alur status, jurnal gaji, slip PDF, publikasi ke portal sopir (bagian slip di `MODUL-SOPIR-GPS.md`).
**KS:** test rumus gaji; maker-checker; sopir hanya melihat slip miliknya yang `dipublikasikan`; jurnal beban dan hutang gaji benar; nilai gaji negatif ditolak.

### Fase 14 — Laporan Keuangan & Penutupan
Semua laporan bagian 9 (PDF/Excel), halaman Rekonsiliasi, Anggaran vs Realisasi, tutup periode dan tutup tahun, konsolidasi.
**KS:** test dataset kecil dihitung manual untuk Laba Rugi, Neraca, Arus Kas; Neraca seimbang; Arus Kas = saldo kas; konsolidasi = jumlah cabang; penutupan periode memblokir posting; watermark DRAF muncul pada periode terbuka.
