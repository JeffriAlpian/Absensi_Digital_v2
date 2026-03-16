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
    protected $sumber;

    /**
     * Create a new job instance.
     * @param string $no_wa Nomor tujuan
     * @param string $pesan Isi pesan utama
     * @param string $sumber Asal fitur (Misal: 'Absensi', 'SPP') - Untuk lapor admin
     */
    public function __construct($no_wa, $pesan, $sumber = 'Sistem Sekolah')
    {
        $this->no_wa = $no_wa;
        $this->pesan = $pesan;
        $this->sumber = $sumber;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // --- LOGIKA ANTI-BANNED ---
        sleep(rand(12, 20));

        // --- AMBIL CONFIG SEKOLAH ---
        $profil = ProfilSekolah::first();
        $secretKey = $profil->key_wa_sidobe ?? null;

        if (empty($secretKey)) {
            $msg = 'WA Job Error: Secret Key kosong / belum disetting.';
            Log::error($msg);
            // Lapor ke Telegram juga biar admin sadar
            $this->sendTelegram('CONFIG ERROR', $msg);
            return;
        }

        // --- KIRIM REQUEST ---
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Secret-Key' => $secretKey
            ])->timeout(30)
                ->post('https://api.sidobe.com/wa/v1/send-message', [
                    'phone' => $this->no_wa,
                    'message' => $this->pesan,
                ]);

            if ($response->ok()) {
                Log::info("WA Terkirim ke: {$this->no_wa}");
                // Sukses tidak perlu lapor telegram agar tidak spam, cukup Log.
            } else {
                $errMsg = "API Error: " . $response->body();
                Log::error($errMsg);

                // PERBAIKAN DISINI: Pakai $this->
                $this->sendTelegram("GAGAL KIRIM", $errMsg);

                // Retry
                if ($this->attempts() < 3) $this->release(60);
            }
        } catch (\Exception $e) {
            $errMsg = 'Exception: ' . $e->getMessage();
            Log::error($errMsg);

            // PERBAIKAN DISINI: Lapor Exception ke Telegram
            $this->sendTelegram("SYSTEM CRASH", $errMsg);

            if ($this->attempts() < 3) $this->release(60);
        }
    }

    /**
     * Kirim Laporan ke Telegram Admin (Monitoring)
     */
    private function sendTelegram($status, $detail)
    {
        // Token & Chat ID (Sebaiknya pindahkan ke .env jika memungkinkan)
        $botToken = "8462176461:AAH8yC5qTlJ9DeX0lHe88OFUEEBXx1C6mm4";
        $chatId   = "7631557592";

        if (!$botToken || !$chatId) {
            return;
        }

        $appUrl = config('app.url');
        // Bersihkan http/https, ambil host-nya saja (misal: smkn1.sch.id)
        $domain = parse_url($appUrl, PHP_URL_HOST) ?? $appUrl;

        // Bersihkan detail jika terlalu panjang (biar url telegram ga error)
        $detail = substr($detail, 0, 1000);

        $text = "<b>Laporan Job WA</b>\n";
        $text .= "Status: <b>{$status}</b>\n";
        $text .= "Fitur: {$this->sumber}\n"; // Admin melihat fitur apa yang mentrigger
        $text .= "Tujuan: {$this->no_wa}\n";
        $text .= "Detail: <code>{$detail}</code>\n";
        $text .= "Sumber: <code>{$domain}</code>";

        try {
            // Gunakan HTTP Client Laravel juga disini biar konsisten
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $text,
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $e) {
            Log::warning("Gagal kirim log ke Telegram: " . $e->getMessage());
        }
        
    }
}
