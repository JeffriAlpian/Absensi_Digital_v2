<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            // Relasi ke Siswa
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->cascadeOnDelete();
            // Relasi ke Guru
            $table->foreignId('guru_id')->nullable()->constrained('guru')->cascadeOnDelete();
            // Relasi ke Device (nullable jika manual)
            $table->foreignId('device_id')->nullable()->constrained('rfid_model')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
             $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            // Opsional: Foto selfie bukti kehadiran
            $table->string('foto_bukti')->nullable();
            $table->enum('status', ['H', 'S', 'I', 'A', 'T'])->default('H');
            $table->string('keterangan', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
