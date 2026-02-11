<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ProfilSekolah;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $no_wa;
    protected $pesan;

    /**
     * Create a new job instance.
     */
    public function __construct($no_wa, $pesan)
    {
        $this->no_wa = $no_wa;
        $this->pesan = $pesan;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // --- LOGIKA ANTI-BANNED (DELAY) ---
        // Kita beri jeda acak antara 5 sampai 15 detik sebelum eksekusi request.
        // Karena worker memproses antrean satu per satu, ini akan membuat efek:
        // Kirim -> Tunggu -> Kirim -> Tunggu.
        sleep(rand(8, 17)); 

        // --- AMBIL CONFIG (Sama seperti service lama) ---
        $profil = ProfilSekolah::first();
        $secretKey = $profil->key_wa_sidobe ?? null;

        if (empty($secretKey)) {
            Log::error('WA Job Error: Secret Key kosong.');
            return;
        }

        // --- KIRIM REQUEST ---
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Secret-Key' => $secretKey
            ])->timeout(20) // Timeout agak lama karena di background
              ->post('https://api.sidobe.com/wa/v1/send-message', [
                'phone' => $this->no_wa,
                'message' => $this->pesan
            ]);

            if ($response->ok()) {
                Log::info("WA Terkirim ke: " . $this->no_wa);
            } else {
                Log::error("WA Gagal (API): " . $response->body());
                // Opsional: Release job kembali ke antrean jika gagal (retry)
                $this->release(60); 
            }

        } catch (\Exception $e) {
            Log::error('WA Job Exception: ' . $e->getMessage());
            // Retry otomatis 3 kali jika koneksi error
            if ($this->attempts() < 3) {
                $this->release(60); // Coba lagi 1 menit kemudian
            }
        }
    }
}