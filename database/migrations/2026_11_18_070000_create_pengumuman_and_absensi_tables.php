<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs');
            $table->string('judul');
            $table->text('isi');
            $table->enum('kategori', ['info', 'keselamatan', 'kebijakan', 'darurat'])->default('info');
            $table->json('target_peran')->nullable();
            $table->boolean('disematkan')->default(false);
            $table->date('terbit_pada');
            $table->date('kedaluwarsa_pada')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pengumuman_dibacas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->constrained('pengumumans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('dibaca_pada');
            $table->timestamps();

            $table->unique(['pengumuman_id', 'user_id']);
        });

        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->foreignId('karyawan_id')->constrained('karyawans');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa', 'libur'])->default('hadir');
            $table->string('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
            $table->index(['cabang_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
        Schema::dropIfExists('pengumuman_dibacas');
        Schema::dropIfExists('pengumumans');
    }
};
