# Progress Proyek FleetFlow

## Catatan / Masalah
- Pembersihan duplikasi Laravel Breeze vs Custom Auth/Layout telah selesai.
- Bug sintaks (`\$`) dan ketidakcocokan nama kolom di `PesananService` & `PengirimanService` telah diperbaiki.
- Seluruh model inti (`Cabang`, `Karyawan`, `Kendaraan`, `Pesanan`, `Pengiriman`, `BbmLog`, `ServisKendaraan`, `SukuCadang`, `StokSukuCadang`, `MutasiSukuCadang`, `BiayaOperasional`, `NomorDokumen`, `Setting`, `User`) kini memiliki relasi lengkap, casting enkripsi, `SoftDeletes`, dan trait isolasi cabang `BelongsToCabang`.

## Fase Selesai
- [x] **Fase 0 — Persiapan** (Selesai)
- [x] **Fase 1 — Database Inti, Peran, Keamanan Dasar, NomorDokumenService** (Selesai)
- [x] **Fase 2 — Layout Admin & Dashboard Operasional Data Nyata** (Selesai)
- [x] **Fase 3 — Data Master (CRUD Cabang, Karyawan, Kendaraan)** (Selesai)
- [x] **Fase 4 — Pesanan, State Machine Status & Pelacakan Publik** (Selesai)
- [x] **Fase 5 — Penjadwalan Multi-Rit, Penugasan Sopir, Eksekusi Pengiriman** (Selesai)
- [x] **Fase 6 — Pencatatan BBM & Validasi Odometer** (Selesai)
- [x] **Fase 7 — Servis Kendaraan & Mutasi Stok Suku Cadang** (Selesai)
- [ ] Fase 8 — Portal Sopir
- [ ] Fase 9 — Pengumuman & Absensi
- [ ] Fase 10-14 — Rilis 2 (Keuangan & Penggajian)
- [ ] Fase 15-18 — Rilis 3 (Pelacakan GPS, Laporan Lanjutan, Pengerasan)
