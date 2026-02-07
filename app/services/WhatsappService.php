<?php

namespace App\Services;

use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function send($no_wa, $pesan)
    {
        // 1. Normalisasi Nomor WA (Logika Anda)
        $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
        
        if (substr($no_wa, 0, 1) === "0") {
            $no_wa = "+62" . substr($no_wa, 1);
        } elseif (substr($no_wa, 0, 2) === "62") {
            $no_wa = "+" . $no_wa;
        } elseif (substr($no_wa, 0, 3) !== "+62") {
            // Nomor tidak valid
            return false;
        }

        // 2. Ambil Secret Key dari Database (Pakai Model)
        $profil = ProfilSekolah::first();
        $secretKey = $profil->key_wa_sidobe ?? null;

        if (empty($secretKey)) {
            Log::error('WA Error: Secret Key belum disetting di database.');
            return false;
        }

        // 3. Kirim via Laravel HTTP Client (Pengganti cURL)
        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Secret-Key' => $secretKey
            ])->timeout(10) // Timeout 10 detik
              ->post('https://api.sidobe.com/wa/v1/send-message', [
                'phone' => $no_wa,
                'message' => $pesan
            ]);

            // 4. Cek Respon
            if ($response->ok()) {
                $resData = $response->json();
                if (isset($resData['is_success']) && $resData['is_success']) {
                    return true; // Berhasil
                }
            }
            
            // Jika gagal (tapi request terkirim)
            Log::error('WA API Fail: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            // Jika koneksi error (Timeout dll)
            Log::error('WA Connection Error: ' . $e->getMessage());
            return false;
        }
    }
}