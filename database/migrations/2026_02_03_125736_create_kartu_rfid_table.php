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
        Schema::create('kartu_rfid', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 64);
            // Bisa punya siswa ATAU guru (Nullable)
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('rfid_model'); // Siapa yang mendaftarkan
            $table->timestamp('registed_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_rfid');
    }
};
