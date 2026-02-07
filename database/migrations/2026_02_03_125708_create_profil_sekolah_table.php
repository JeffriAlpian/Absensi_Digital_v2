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
        Schema::create('profil_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah', 150);
            $table->string('alamat_sekolah', 255)->nullable();
            $table->string('kepala_sekolah', 100)->nullable();
            $table->string('nip_kepala_sekolah', 20)->nullable();
            $table->string('logo_sekolah', 150)->nullable();
            $table->string('desain_kartu', 20)->nullable();
            $table->string('key_wa_sidobe', 200)->nullable();
            $table->string('desain_kartu_siswa_depan')->nullable();
            $table->string('desain_kartu_siswa_belakang')->nullable();
            $table->string('desain_kartu_guru_depan')->nullable();
            $table->string('desain_kartu_guru_belakang')->nullable();
            $table->time('jam_masuk_guru')->nullable()->default('07:30:00');
            $table->time('jam_pulang_guru')->nullable()->default('14:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_sekolah');
    }
};
