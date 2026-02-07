<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\WhatsappService; // Panggil Service WA
// Import Model
use App\Models\RfidModel;
use App\Models\KartuRfid;
use App\Models\Absensi;
use App\Models\ProfilSekolah;

class AbsensiRfidController extends Controller
{
    public function catatAbsensi(Request $request)
    {
        // Set Timezone & Waktu
        $now = Carbon::now('Asia/Jakarta');
        $tanggal = $now->format('Y-m-d');
        $jam = $now->format('H:i:s');
        
        // --- 1. VALIDASI INPUT & API KEY ---
        if (!$request->has(['api_key', 'uid'])) {
            return response()->json(["status" => "error", "message" => "Data JSON tidak lengkap"]);
        }

        $device = RfidModel::where('api_key', $request->api_key)->first();
        
        if (!$device) {
            return response()->json(["status" => "error", "message" => "API Key tidak valid"]);
        }

        // Update Last Seen Device
        $device->update(['last_seen' => $now]);

        // --- 2. CARI PEMILIK KARTU ---
        // Kita gunakan Eloquent relationship (asumsi model KartuRfid punya relasi ke siswa dan guru)
        $kartu = KartuRfid::where('uid', $request->uid)
                    ->with(['siswa.kelas', 'guru']) // Load relasi agar hemat query
                    ->first();

        if (!$kartu) {
            return response()->json(["status" => "tidak_terdaftar", "message" => "Kartu RFID (UID: {$request->uid}) belum terdaftar"]);
        }

        // Variabel penampung
        $nama = "Unknown";
        $userId = null;
        $userType = null;
        $noWa = null;
        $pesanWa = "";
        $statusAbsen = "error";
        $msg = "Error processing";

        // Tentukan Tipe User
        if ($kartu->siswa && $kartu->siswa->status == 'aktif') {
            $userType = 'siswa';
            $userId = $kartu->siswa->id;
            $nama = $kartu->siswa->nama;
            $noWa = $kartu->siswa->no_wa;
            
            // Proses Absensi Siswa
            $result = $this->prosesAbsensiSiswa($kartu->siswa, $device->id, $now, $tanggal, $jam);
        } 
        elseif ($kartu->guru) {
            $userType = 'guru';
            $userId = $kartu->guru->id;
            $nama = $kartu->guru->nama;
            $noWa = $kartu->guru->no_wa;
            
            // Proses Absensi Guru
            $result = $this->prosesAbsensiGuru($kartu->guru, $device->id, $now, $tanggal, $jam);
        } 
        else {
            return response()->json(["status" => "error", "message" => "Kartu terdaftar tapi user tidak aktif/ditemukan"]);
        }

        // Ambil hasil proses
        $statusAbsen = $result['status'];
        $msg = $result['msg'];
        $pesanWa = $result['pesan_wa'] ?? "";

        // --- 3. KIRIM WA (MENGGUNAKAN SERVICE) ---
        $waStatus = "";
        if (!empty($pesanWa) && !empty($noWa)) {
            // Panggil Service WA yang sudah kita buat
            $isSent = WhatsappService::send($noWa, $pesanWa);
            $waStatus = $isSent ? "📲 WA terkirim." : "⚠️ Gagal kirim WA.";
        }

        // --- 4. RETURN JSON KE ALAT ---
        return response()->json([
            "status" => $statusAbsen,
            "message" => $msg,
            "nama" => $nama,
            "user_id" => $userId,
            "user_type" => $userType,
            "tanggal" => $tanggal,
            "jam" => $jam,
            "wa_status" => $waStatus
        ]);
    }

    // --- PRIVATE FUNCTION: LOGIKA SISWA ---
    private function prosesAbsensiSiswa($siswa, $deviceId, $now, $tanggal, $jam)
    {
        // Ambil jam kelas
        $profil = ProfilSekolah::first();
        $jamMasukMax = $siswa->kelas->jam_masuk ?? '07:30:00';
        $jamPulangMin = $siswa->kelas->jam_pulang ?? '13:00:00';

        // Cek Absensi Hari Ini
        $absensi = Absensi::where('siswa_id', $siswa->id)->where('tanggal', $tanggal)->first();

        if (!$absensi) {
            // --- ABSEN MASUK ---
            $keterangan = $now->gt(Carbon::parse($tanggal . ' ' . $jamMasukMax)) ? 'Terlambat' : '';
            
            Absensi::create([
                'siswa_id' => $siswa->id,
                'device_id' => $deviceId,
                'tanggal' => $tanggal,
                'jam_masuk' => $jam,
                'status' => 'H', // Hadir
                'keterangan' => $keterangan
            ]);

            $pesanWa = "Info Absen:\nAnanda *$siswa->nama* ($siswa->nisn)\ntelah *ABSEN MASUK*" . ($keterangan == 'Terlambat' ? ' (TERLAMBAT)' : '') . "\npada " . $now->format('d-m-Y H:i') . ". Terima kasih.\n\n> " . $profil->nama_sekolah;

            return ['status' => 'masuk', 'msg' => "Absen masuk " . ($keterangan ?: 'berhasil'), 'pesan_wa' => $pesanWa];

        } else {
            // --- ABSEN PULANG ---
            if (empty($absensi->jam_pulang)) {
                // Cek apakah sudah waktunya pulang
                if ($now->lt(Carbon::parse($tanggal . ' ' . $jamPulangMin))) {
                    return ['status' => 'sudah_masuk', 'msg' => "Belum jam pulang (Min: $jamPulangMin)."];
                }

                $absensi->update([
                    'jam_pulang' => $jam,
                    'device_id' => $deviceId // Update device kepulangan jika beda
                ]);

                $pesanWa = "Info Absen:\nAnanda *$siswa->nama* ($siswa->nisn)\ntelah *ABSEN PULANG*\npada " . $now->format('d-m-Y H:i') . ". Terima kasih.\n\n> " . $profil->nama_sekolah;

                return ['status' => 'pulang', 'msg' => "Absen pulang berhasil", 'pesan_wa' => $pesanWa];
            } else {
                return ['status' => 'sudah_masuk_pulang', 'msg' => "Sudah absen masuk dan pulang hari ini"];
            }
        }
    }

    // --- PRIVATE FUNCTION: LOGIKA GURU ---
    private function prosesAbsensiGuru($guru, $deviceId, $now, $tanggal, $jam)
    {
        // Ambil pengaturan jam guru dari profil sekolah (karena 1 baris, ambil first)
        $profil = ProfilSekolah::first();
        $jamMasukMax = $profil->jam_masuk_guru ?? '07:00:00';
        $jamPulangMin = $profil->jam_pulang_guru ?? '14:00:00';

        // Cek Absensi Hari Ini
        $absensi = Absensi::where('guru_id', $guru->id)->where('tanggal', $tanggal)->first();

        if (!$absensi) {
            // --- MASUK ---
            $keterangan = $now->gt(Carbon::parse($tanggal . ' ' . $jamMasukMax)) ? 'Terlambat' : '';

            Absensi::create([
                'guru_id' => $guru->id,
                'device_id' => $deviceId,
                'tanggal' => $tanggal,
                'jam' => $jam,
                'status' => 'H',
                'keterangan' => $keterangan
            ]);

            $pesanWa = "Info Absen: Bapak/Ibu *$guru->nama* telah *ABSEN MASUK*" . ($keterangan == 'Terlambat' ? ' (TERLAMBAT)' : '') . " pada " . $now->format('d-m-Y H:i') . ".";

            return ['status' => 'masuk', 'msg' => "Absen masuk " . ($keterangan ?: 'berhasil'), 'pesan_wa' => $pesanWa];

        } else {
            // --- PULANG ---
            if (empty($absensi->jam_pulang)) {
                if ($now->lt(Carbon::parse($tanggal . ' ' . $jamPulangMin))) {
                    return ['status' => 'sudah_masuk', 'msg' => "Belum jam pulang (Min: $jamPulangMin)."];
                }

                $absensi->update([
                    'jam_pulang' => $jam,
                    'device_id' => $deviceId
                ]);

                $pesanWa = "Info Absen: Bapak/Ibu *$guru->nama* telah *ABSEN PULANG* pada " . $now->format('d-m-Y H:i') . ".";

                return ['status' => 'pulang', 'msg' => "Absen pulang berhasil", 'pesan_wa' => $pesanWa];
            } else {
                return ['status' => 'sudah_masuk_pulang', 'msg' => "Sudah absen masuk dan pulang hari ini"];
            }
        }
    }
}