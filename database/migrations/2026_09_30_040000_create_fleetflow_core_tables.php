<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. cabangs
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('kota')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. karyawans
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs');
            $table->string('kode');
            $table->string('nama');
            $table->enum('jabatan', ['sopir','kernet','mekanik','staf_gudang','staf_admin']);
            $table->string('no_hp')->nullable();
            $table->text('no_sim')->nullable(); // encrypted
            $table->string('jenis_sim')->nullable();
            $table->date('sim_berlaku_sampai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['cabang_id', 'kode']);
        });

        // 3. update users
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->after('password');
            $table->foreignId('karyawan_id')->nullable()->unique()->constrained('karyawans')->after('cabang_id');
            $table->boolean('wajib_ganti_password')->default(true)->after('karyawan_id');
            $table->boolean('aktif')->default(true)->after('wajib_ganti_password');
            // email should be nullable
        });
        // Make email nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // 4. update activity_log (needs cabang_id)
        Schema::table('activity_log', function (Blueprint $table) {
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->after('subject_id');
        });

        // 5. kendaraans
        Schema::create('kendaraans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->string('kode');
            $table->string('plat_nomor')->unique();
            $table->enum('jenis', ['motor','pickup','box','truk']);
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->year('tahun')->nullable();
            $table->decimal('kapasitas_kg', 10, 2);
            $table->string('jenis_bbm')->nullable();
            $table->integer('odometer_terakhir')->default(0);
            $table->string('status')->default('tersedia'); // tersedia, bertugas, servis, nonaktif
            $table->date('stnk_berlaku_sampai')->nullable();
            $table->date('kir_berlaku_sampai')->nullable();
            $table->date('pajak_berlaku_sampai')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['cabang_id', 'status']);
        });

        // 6. pengirimans (needs to be created before pesanans because pesanans has pengiriman_id)
        Schema::create('pengirimans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->string('kode')->unique();
            $table->date('tanggal_rencana');
            $table->foreignId('kendaraan_id')->constrained('kendaraans');
            $table->foreignId('sopir_id')->constrained('karyawans');
            $table->foreignId('kernet_id')->nullable()->constrained('karyawans');
            $table->string('tujuan_ringkas')->nullable();
            $table->dateTime('waktu_mulai_rencana')->nullable();
            $table->dateTime('estimasi_tiba')->nullable();
            $table->integer('rit_ke')->default(1);
            $table->integer('odometer_awal')->nullable();
            $table->integer('odometer_akhir')->nullable();
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            $table->string('status')->default('terjadwal'); // terjadwal, berjalan, selesai, batal
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->text('override_alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['kendaraan_id', 'waktu_mulai_rencana']);
            $table->index(['sopir_id', 'waktu_mulai_rencana']);
        });

        // 7. pesanans
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->string('kode_resi')->unique();
            $table->date('tanggal_pesan');
            $table->string('nama_pengirim')->nullable();
            $table->text('no_hp_pengirim')->nullable(); // encrypted
            $table->text('alamat_asal')->nullable();
            $table->string('kota_asal')->nullable();
            $table->string('nama_penerima');
            $table->text('no_hp_penerima')->nullable(); // encrypted
            $table->text('alamat_tujuan');
            $table->string('kota_tujuan');
            $table->text('deskripsi_barang')->nullable();
            $table->decimal('berat_kg', 10, 2);
            $table->integer('jumlah_koli')->default(1);
            $table->decimal('tarif', 15, 2)->default(0);
            $table->string('status')->default('draf');
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengirimans');
            $table->string('token_publik')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['cabang_id', 'status']);
        });

        // 8. pesanan_status_logs
        Schema::create('pesanan_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans');
            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->dateTime('waktu');
            // From MODUL-SOPIR-GPS.md
            $table->string('sumber')->default('admin');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps(); // Append only
        });

        // 9. suku_cadangs
        Schema::create('suku_cadangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('harga_standar', 15, 2)->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. stok_suku_cadangs
        Schema::create('stok_suku_cadangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('suku_cadang_id')->constrained('suku_cadangs');
            $table->integer('qty')->default(0);
            $table->timestamps();
            $table->unique(['cabang_id', 'suku_cadang_id']);
        });

        // 11. mutasi_suku_cadangs
        Schema::create('mutasi_suku_cadangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('suku_cadang_id')->constrained('suku_cadangs');
            $table->enum('tipe', ['masuk','keluar_servis','penyesuaian']);
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->string('referensi_type')->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->dateTime('tanggal');
            $table->timestamps();
        });

        // 12. bbm_logs
        Schema::create('bbm_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('kendaraan_id')->constrained('kendaraans');
            $table->foreignId('sopir_id')->nullable()->constrained('karyawans');
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengirimans');
            $table->date('tanggal');
            $table->decimal('liter', 8, 2);
            $table->decimal('harga_per_liter', 15, 2);
            $table->decimal('total', 15, 2);
            $table->integer('odometer');
            $table->boolean('tangki_penuh')->default(false);
            $table->string('spbu')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            // From MODUL-SOPIR-GPS.md
            $table->string('sumber')->default('admin');
            $table->string('status_verifikasi')->default('terverifikasi'); // terverifikasi, menunggu_verifikasi, ditolak
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->dateTime('diverifikasi_pada')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->string('bukti_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            $table->index(['kendaraan_id', 'tanggal']);
        });

        // 13. servis_kendaraans
        Schema::create('servis_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('kendaraan_id')->constrained('kendaraans');
            $table->enum('jenis', ['berkala','perbaikan','ganti_ban','kir','lainnya']);
            $table->string('status')->default('dijadwalkan'); // dijadwalkan, dikerjakan, selesai, batal
            $table->date('tanggal_masuk');
            $table->date('tanggal_selesai')->nullable();
            $table->integer('odometer');
            $table->string('bengkel')->nullable();
            $table->decimal('biaya_jasa', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->integer('servis_berikutnya_km')->nullable();
            $table->date('servis_berikutnya_tanggal')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 14. servis_suku_cadangs
        Schema::create('servis_suku_cadangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servis_id')->constrained('servis_kendaraans');
            $table->foreignId('suku_cadang_id')->constrained('suku_cadangs');
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->timestamps();
        });

        // 15. biaya_operasionals
        Schema::create('biaya_operasionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraans');
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengirimans');
            $table->enum('jenis', ['tol','parkir','denda','lainnya']);
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            // From MODUL-SOPIR-GPS.md
            $table->string('sumber')->default('admin');
            $table->string('status_verifikasi')->default('terverifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->dateTime('diverifikasi_pada')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->string('bukti_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 16. nomor_dokumens
        Schema::create('nomor_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs');
            $table->string('jenis');
            $table->string('periode')->nullable();
            $table->integer('terakhir')->default(0);
            $table->timestamps();
            $table->unique(['cabang_id', 'jenis', 'periode']);
        });

        // 17. settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('nomor_dokumens');
        Schema::dropIfExists('biaya_operasionals');
        Schema::dropIfExists('servis_suku_cadangs');
        Schema::dropIfExists('servis_kendaraans');
        Schema::dropIfExists('bbm_logs');
        Schema::dropIfExists('mutasi_suku_cadangs');
        Schema::dropIfExists('stok_suku_cadangs');
        Schema::dropIfExists('suku_cadangs');
        Schema::dropIfExists('pesanan_status_logs');
        Schema::dropIfExists('pesanans');
        Schema::dropIfExists('pengirimans');
        Schema::dropIfExists('kendaraans');
        
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn('cabang_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropForeign(['cabang_id']);
            $table->dropColumn(['username', 'cabang_id', 'karyawan_id', 'wajib_ganti_password', 'aktif']);
            $table->string('email')->nullable(false)->change();
        });
        
        Schema::dropIfExists('karyawans');
        Schema::dropIfExists('cabangs');
    }
};
