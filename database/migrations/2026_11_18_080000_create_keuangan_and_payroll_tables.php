<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->text('no_rekening')->nullable()->after('sim_berlaku_sampai');
        });

        // Fase 10 — Fondasi Akuntansi
        Schema::create('akuns', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->enum('tipe', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']);
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->foreignId('induk_id')->nullable()->constrained('akuns');
            $table->enum('kategori_arus_kas', ['operasi', 'investasi', 'pendanaan'])->nullable();
            $table->boolean('is_kas')->default(false);
            $table->boolean('aktif')->default(true);
            $table->boolean('sistem')->default(false);
            $table->timestamps();
        });

        Schema::create('periode_akuntansis', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->foreignId('ditutup_oleh')->nullable()->constrained('users');
            $table->dateTime('ditutup_pada')->nullable();
            $table->text('alasan_buka_kembali')->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'bulan']);
        });

        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs');
            $table->string('no_jurnal')->unique();
            $table->date('tanggal');
            $table->foreignId('periode_id')->constrained('periode_akuntansis');
            $table->text('keterangan');
            $table->enum('jenis', ['manual', 'otomatis', 'penutup', 'balik'])->default('manual');
            $table->string('peristiwa')->nullable();
            $table->string('sumber_type')->nullable();
            $table->unsignedBigInteger('sumber_id')->nullable();
            $table->enum('status', ['draf', 'posted'])->default('draf');
            $table->foreignId('membalik_jurnal_id')->nullable()->constrained('jurnals');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->dateTime('diposting_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'status']);
        });

        Schema::create('jurnal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnals')->cascadeOnDelete();
            $table->foreignId('akun_id')->constrained('akuns');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraans');
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengirimans');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->timestamps();
        });

        // Fase 11 — Penagihan & Piutang
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('npwp')->nullable(); // encrypted
            $table->text('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->string('no_hp')->nullable();
            $table->integer('termin_hari')->default(14);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('kota_asal');
            $table->string('kota_tujuan');
            $table->string('jenis_kendaraan')->nullable();
            $table->decimal('harga_per_kg', 15, 2);
            $table->decimal('tarif_minimum', 15, 2)->default(0);
            $table->date('berlaku_sejak');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->string('no_invoice')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->date('tanggal');
            $table->date('jatuh_tempo');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ppn_persen', 5, 2)->default(0);
            $table->decimal('ppn_nominal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('terbayar', 15, 2)->default(0);
            $table->enum('status', ['draf', 'terbit', 'sebagian', 'lunas', 'batal'])->default('draf');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanans');
            $table->string('deskripsi');
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('harga', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->string('no_pembayaran')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->date('tanggal');
            $table->string('metode')->default('transfer');
            $table->foreignId('akun_kas_id')->constrained('akuns');
            $table->decimal('jumlah_diterima', 15, 2);
            $table->decimal('potongan_pajak', 15, 2)->default(0);
            $table->string('referensi')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            $table->enum('status', ['aktif', 'batal'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pembayaran_alokasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->decimal('jumlah_alokasi', 15, 2);
            $table->decimal('potongan_pajak', 15, 2)->default(0);
            $table->timestamps();
        });

        // Fase 12 — Hutang Usaha, Aset Tetap & Anggaran
        Schema::create('pemasoks', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->enum('jenis', ['spbu', 'bengkel', 'supplier', 'lainnya'])->default('supplier');
            $table->string('kontak')->nullable();
            $table->integer('termin_hari')->default(14);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tagihan_pemasoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->string('no_tagihan')->unique();
            $table->foreignId('pemasok_id')->constrained('pemasoks');
            $table->date('tanggal');
            $table->date('jatuh_tempo');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('terbayar', 15, 2)->default(0);
            $table->enum('status', ['terbit', 'sebagian', 'lunas', 'batal'])->default('terbit');
            $table->string('sumber_type')->nullable();
            $table->unsignedBigInteger('sumber_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pembayaran_pemasoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('tagihan_id')->constrained('tagihan_pemasoks');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->foreignId('akun_kas_id')->constrained('akuns');
            $table->string('referensi')->nullable();
            $table->timestamps();
        });

        Schema::create('aset_tetaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraans');
            $table->string('nama');
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->integer('umur_bulan');
            $table->string('metode')->default('garis_lurus');
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'dilepas'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('penyusutans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_id')->constrained('aset_tetaps');
            $table->foreignId('periode_id')->constrained('periode_akuntansis');
            $table->decimal('jumlah', 15, 2);
            $table->foreignId('jurnal_id')->nullable()->constrained('jurnals');
            $table->timestamps();

            $table->unique(['aset_id', 'periode_id']);
        });

        Schema::create('anggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->foreignId('akun_id')->constrained('akuns');
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();

            $table->unique(['cabang_id', 'tahun', 'bulan', 'akun_id']);
        });

        // Fase 13 — Penggajian
        Schema::create('komponen_gajis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->enum('jenis', ['pendapatan', 'potongan']);
            $table->enum('tipe_hitung', ['tetap', 'per_hari_hadir', 'per_ritase', 'manual']);
            $table->decimal('nominal_default', 15, 2)->default(0);
            $table->foreignId('akun_id')->nullable()->constrained('akuns');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('profil_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans');
            $table->foreignId('komponen_id')->constrained('komponen_gajis');
            $table->decimal('nominal', 15, 2);
            $table->date('berlaku_sejak');
            $table->timestamps();
        });

        Schema::create('periode_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->enum('status', ['draf', 'dihitung', 'disetujui', 'dibayar', 'dipublikasikan'])->default('draf');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->date('tanggal_bayar')->nullable();
            $table->foreignId('akun_kas_id')->nullable()->constrained('akuns');
            $table->timestamps();

            $table->unique(['cabang_id', 'tahun', 'bulan']);
        });

        Schema::create('slip_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_gaji_id')->constrained('periode_gajis')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawans');
            $table->string('no_slip')->unique();
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0);
            $table->integer('hari_hadir')->default(0);
            $table->integer('jumlah_ritase')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('slip_gaji_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slip_id')->constrained('slip_gajis')->cascadeOnDelete();
            $table->foreignId('komponen_id')->nullable()->constrained('komponen_gajis');
            $table->string('keterangan');
            $table->enum('jenis', ['pendapatan', 'potongan']);
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });

        Schema::create('kasbons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('karyawan_id')->constrained('karyawans');
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('cicilan_per_bulan', 15, 2);
            $table->decimal('sisa', 15, 2);
            $table->enum('status', ['aktif', 'lunas'])->default('aktif');
            $table->foreignId('jurnal_id')->nullable()->constrained('jurnals');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasbons');
        Schema::dropIfExists('slip_gaji_items');
        Schema::dropIfExists('slip_gajis');
        Schema::dropIfExists('periode_gajis');
        Schema::dropIfExists('profil_gajis');
        Schema::dropIfExists('komponen_gajis');
        Schema::dropIfExists('anggarans');
        Schema::dropIfExists('penyusutans');
        Schema::dropIfExists('aset_tetaps');
        Schema::dropIfExists('pembayaran_pemasoks');
        Schema::dropIfExists('tagihan_pemasoks');
        Schema::dropIfExists('pemasoks');
        Schema::dropIfExists('pembayaran_alokasis');
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('tarifs');
        Schema::dropIfExists('pelanggans');
        Schema::dropIfExists('jurnal_details');
        Schema::dropIfExists('jurnals');
        Schema::dropIfExists('periode_akuntansis');
        Schema::dropIfExists('akuns');

        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('no_rekening');
        });
    }
};
