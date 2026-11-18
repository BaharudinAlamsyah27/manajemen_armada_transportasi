<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->string('traccar_device_id')->nullable()->after('pajak_berlaku_sampai');
        });

        Schema::create('sesi_pelacakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->constrained('pengirimans');
            $table->foreignId('kendaraan_id')->constrained('kendaraans');
            $table->foreignId('sopir_id')->constrained('karyawans');
            $table->dateTime('mulai');
            $table->dateTime('selesai')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'terputus'])->default('aktif');
            $table->dateTime('persetujuan_sopir_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('lokasi_pings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_pelacakan')->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->constrained('kendaraans');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('akurasi_m', 8, 2)->nullable();
            $table->decimal('kecepatan_kmh', 8, 2)->nullable();
            $table->decimal('arah', 6, 2)->nullable();
            $table->dateTime('waktu_perangkat')->nullable();
            $table->dateTime('waktu_server');
            $table->enum('sumber', ['web', 'traccar', 'lain'])->default('web');
            $table->boolean('valid')->default(true);
            $table->timestamps();

            $table->index(['kendaraan_id', 'waktu_server']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_pings');
        Schema::dropIfExists('sesi_pelacakan');
        Schema::table('kendaraans', function (Blueprint $table) {
            $table->dropColumn('traccar_device_id');
        });
    }
};
