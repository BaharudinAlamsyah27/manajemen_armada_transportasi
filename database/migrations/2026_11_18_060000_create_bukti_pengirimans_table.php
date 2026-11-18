<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MODUL-SOPIR-GPS.md bagian 3 & 5 — bukti pengiriman (POD), disimpan di disk privat.
        Schema::create('bukti_pengirimans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans');
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengirimans');
            // foto | tanda_tangan | manual (input admin tanpa foto, lihat ASUMSI A19)
            $table->string('jenis')->default('foto');
            $table->string('path')->nullable();
            $table->string('nama_penerima');
            $table->text('catatan')->nullable();
            $table->foreignId('diambil_oleh')->nullable()->constrained('users');
            $table->dateTime('waktu');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('ukuran')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();

            $table->index('pesanan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_pengirimans');
    }
};
