<?php

namespace App\Services;

use App\Jobs\SendWhatsappJob;

class WhatsappService
{
    public static function send($no_wa, $pesan)
    {
        // 1. Normalisasi Nomor WA
        $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
        
        if (substr($no_wa, 0, 1) === "0") {
            $no_wa = "+62" . substr($no_wa, 1);
        } elseif (substr($no_wa, 0, 2) === "62") {
            $no_wa = "+" . $no_wa;
        } elseif (substr($no_wa, 0, 3) !== "+62") {
            return false;
        }

        // 2. Masukkan ke Antrean (Dispatch)
        // onQueue('whatsapp') opsional, untuk memisahkan jalur antrean jika perlu
        SendWhatsappJob::dispatch($no_wa, $pesan);

        return true; 
    }
}