<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsappService;

// Import Model
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\ProfilSekolah;

class AbsensiController extends Controller
{

    public function scanIndex()
    {
        return view('absensi.scan.index');
    }

    public function manualIndex(Request $request)
    {
        // 1. Ambil Input Filter
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kategori = $request->input('kategori', 'siswa');
        $kelasId = $request->input('kelas_id'); // Sesuaikan nama input select di view
        $statusFilter = $request->input('status'); // Input baru: 'belum_absen', 'H', 'S', 'I', 'A', atau null (semua)

        $listKelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $dataList = [];

        // 2. Tentukan Model Berdasarkan Kategori
        if ($kategori == 'siswa') {
            $query = Siswa::query()->where('status', 'aktif'); // Pastikan hanya siswa aktif

            // Filter Kelas
            if ($kelasId) {
                $query->where('id_kelas', $kelasId);
            }
        } else {
            $query = Guru::query()->where('status', 'aktif');
        }

        // 3. Eager Load Absensi (Agar data absen tampil di list)
        $query->with(['absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }]);

        // 4. LOGIKA FILTER STATUS (Inti Pertanyaan Anda)
        if ($statusFilter) {
            if ($statusFilter == 'belum_absen') {
                // A. Tampilkan yang BELUM ada data absen hari ini
                $query->whereDoesntHave('absensi', function ($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                });
            } else {
                // B. Tampilkan berdasarkan status spesifik (H, S, I, A)
                $query->whereHas('absensi', function ($q) use ($tanggal, $statusFilter) {
                    $q->whereDate('tanggal', $tanggal)
                        ->where('status', $statusFilter);
                });
            }
        }

        // 5. Eksekusi Query
        // Urutkan nama abjad agar rapi
        $dataList = $query->orderBy('nama', 'asc')->get();

        return view('absensi.manual.index', compact(
            'tanggal',
            'kategori',
            'kelasId',
            'statusFilter', // Kirim balik ke view agar dropdown terpilih
            'listKelas',
            'dataList'
        ));
    }

    // =========================================================================
    // Input Manual Absensi Siswa
    // =========================================================================

    public function storeManual(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required',
            'status' => 'required',
            'tanggal' => 'required|date',
        ]);

        $siswa = Siswa::findOrFail($request->siswa_id);

        // Cek apakah data sudah ada (Update) atau belum (Create)
        $absensi = Absensi::updateOrCreate(
            [
                'siswa_id' => $request->siswa_id,
                'tanggal' => $request->tanggal
            ],
            [
                'status' => $request->status,
                'keterangan' => $request->keterangan
            ]
        );

        // Pesan WA
        $aksi = $absensi->wasRecentlyCreated ? "ditambahkan" : "diubah";
        $pesan = "Info Absen:\nAnanda *{$siswa->nama}* ({$siswa->nisn})\npada tanggal {$request->tanggal}\ntelah {$aksi} statusnya menjadi: *{$request->status}*.\nKeterangan: {$request->keterangan}.\nTerima kasih.\n\nNote: Tidak perlu membalas pesan ini.\n\n> SMK Darul A'mal";

        $kirim = WhatsappService::send($siswa->no_wa, $pesan);

        $msg = $kirim ? "✅ Data disimpan & WA terkirim." : "⚠️ Data disimpan, tapi WA gagal.";

        $user = Auth::user();
        // 1. Cek Role User
        if ($user->role === 'guru') {
            return redirect()->route('dashboard.guru.manual.index', [
                'tanggal' => $request->tanggal,
                'kelas' => $request->kelas_filter // Kirim balik filter kelas agar tidak reset
            ])->with('message', $msg);
        } else {
            return redirect()->route('absensi.manual.index', [
                'tanggal' => $request->tanggal,
                'kelas' => $request->kelas_filter // Kirim balik filter kelas agar tidak reset
            ])->with('message', $msg);
        }
    }

    // Handle Hadir Semua
    public function storeAll(Request $request)
    {
        $tanggal = $request->tanggal;
        $kelasId = $request->kelas_filter;

        $siswas = Siswa::when($kelasId, function ($q) use ($kelasId) {
            $q->where('id_kelas', $kelasId);
        })->get();

        foreach ($siswas as $siswa) {
            // Logic: Update or Create Status 'H'
            $absensi = Absensi::updateOrCreate(
                ['siswa_id' => $siswa->id, 'tanggal' => $tanggal],
                ['status' => 'H', 'keterangan' => '']
            );

            // Kirim WA (Hati-hati spam jika data banyak, sebaiknya gunakan Job Queue)
            $aksi = $absensi->wasRecentlyCreated ? "ditambahkan" : "diubah";
            $pesan = "Info Absen:\nAnanda *{$siswa->nama}*\npada tanggal {$tanggal}\ntelah {$aksi} statusnya menjadi: H (Hadir).\nTerima kasih.\n\n> SMK Darul A'mal";

            WhatsappService::send($siswa->no_wa, $pesan);
        }

        return redirect()->route('absensi.manual.index', [
            'tanggal' => $tanggal,
            'kelas' => $kelasId
        ])->with('message', '✅ Semua siswa ditandai Hadir.');
    }

    // =========================================================================
    // ABSENSI SCAN QR CODE (BISA SISWA & GURU)
    // =========================================================================
    public function storeScan(Request $request)
    {
        // 1. Set Waktu Saat Ini
        $now = Carbon::now('Asia/Jakarta');

        // 2. Validasi Input
        $request->validate([
            'qrcode' => 'required',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
        ]);

        $qrCode = $request->qrcode;
        $lat    = $request->latitude;
        $long   = $request->longitude;

        // ---------------------------------------------------------------------
        // A. CEK DATA SISWA (Prioritas 1)
        // ---------------------------------------------------------------------
        $siswa = Siswa::where('nisn', $qrCode)->first();

        if ($siswa) {
            // Cek Status Aktif
            if ($siswa->status != 'aktif') {
                return response()->json([
                    "status" => "error",
                    "message" => "❌ Status siswa (<b>$siswa->nama</b>) tidak aktif."
                ]);
            }

            // Jalankan Logika Absensi Siswa
            $result = $this->logikaAbsensiSiswa($siswa, $now);

            // Kirim WA Siswa
            $waStatus = "";
            if (!empty($result['pesan_wa']) && !empty($siswa->no_wa)) {
                // Gunakan Job jika ingin background process, atau direct service
                // SendWhatsappJob::dispatch($siswa->no_wa, $result['pesan_wa'], 'Absensi Scan'); 
                $isSent = WhatsappService::send($siswa->no_wa, $result['pesan_wa']);
                $waStatus = $isSent ? "📲 WA terkirim." : "⚠️ WA gagal.";
            }

            return response()->json([
                "status"    => "success",
                "message"   => $result['msg'] . "<br><small>$waStatus</small>",
                "data"      => [
                    "nama" => $siswa->nama,
                    "role" => "Siswa",
                    "jam"  => $now->format('H:i:s'),
                    "type" => $result['status']
                ]
            ]);
        }

        // ---------------------------------------------------------------------
        // B. CEK DATA GURU (Prioritas 2 - Jika Siswa Tidak Ditemukan)
        // ---------------------------------------------------------------------
        // Asumsi: QR Guru berisi NIP atau Kode Unik Guru
        $guru = Guru::where('nip', $qrCode)
            ->first();

        if ($guru) {
            // Cek Status Aktif Guru (Opsional)
            if (isset($guru->status) && $guru->status != 'aktif') {
                return response()->json(["status" => "error", "message" => "❌ Status Guru tidak aktif."]);
            }

            // Validasi Lokasi untuk Guru (Opsional saat Scan QR)
            // Jika scan QR dilakukan di Kiosk Sekolah, kita anggap lokasinya valid (Sekolah).
            // Jika Lat/Long kosong (PC tanpa GPS), kita ambil koordinat sekolah default.
            $profil = ProfilSekolah::first();
            if (empty($lat) || empty($long)) {
                $lat  = $profil->latitude;
                $long = $profil->longitude;
            }

            // Jalankan Logika Absensi Guru
            $result = $this->logikaAbsensiGuru($guru, $now, $lat, $long);

            // Kirim WA Guru
            $waStatus = "";
            if (!empty($result['pesan_wa']) && !empty($guru->no_wa)) {
                $isSent = WhatsappService::send($guru->no_wa, $result['pesan_wa']);
                // Kirim notif ke Waka Kurikulum juga jika perlu (seperti di function storeGuru)
                if (!empty($profil->wakur_wa)) {
                    WhatsappService::send($profil->wakur_wa, $result['pesan_wa']);
                }
                $waStatus = $isSent ? "📲 WA terkirim." : "⚠️ WA gagal.";
            }

            return response()->json([
                "status"    => "success",
                "message"   => $result['msg'] . "<br><small>$waStatus</small>",
                "data"      => [
                    "nama" => $guru->nama,
                    "role" => "Guru",
                    "jam"  => $now->format('H:i:s'),
                    "type" => $result['status']
                ]
            ]);
        }

        // ---------------------------------------------------------------------
        // C. DATA TIDAK DITEMUKAN
        // ---------------------------------------------------------------------
        return response()->json([
            "status" => "error",
            "message" => "❌ QR Code tidak dikenali!<br><small>Bukan Siswa maupun Guru.</small>"
        ], 404);
    }


    // =========================================================================
    // ABSENSI SISWA (SCAN QR CODE)
    // =========================================================================
    // public function storeScan(Request $request)
    // {
    //     // 1. Set Waktu Saat Ini (Carbon Object)
    //     $now = Carbon::now('Asia/Jakarta');

    //     // 2. Validasi
    //     $request->validate([
    //         'qrcode' => 'required',
    //         'latitude' => 'nullable',
    //         'longitude' => 'nullable',
    //     ]);

    //     $user = Auth::user();

    //     if ($user && $user->role == 'guru') {
    //         // A. Ambil Data Lokasi Sekolah
    //         $profil = ProfilSekolah::first();
    //         if (!$profil) {
    //             return response()->json(['status' => 'error', 'message' => '❌ Data Profil Sekolah (Koordinat) belum disetting!']);
    //         }

    //         // B. Ambil Input Koordinat dari Guru
    //         $latGuru  = $request->latitude;
    //         $longGuru = $request->longitude;

    //         // C. Validasi Jika GPS Browser Mati/Tidak Terkirim
    //         if (empty($latGuru) || empty($longGuru)) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => '📍 Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi di browser.'
    //             ]);
    //         }

    //         // D. Hitung Jarak (Panggil fungsi helper di bawah)
    //         $jarakMeter = $this->hitungJarak($latGuru, $longGuru, $profil->latitude, $profil->longitude);
    //         $batasRadius = $profil->radius ?? 50; // Default 50 meter jika null

    //         // E. Tolak Jika Diluar Radius
    //         if ($jarakMeter > $batasRadius) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => "❌ Anda berada di luar jangkauan!<br>Jarak: " . round($jarakMeter) . "m (Max: {$batasRadius}m)"
    //             ]);
    //         }
    //     }

    //     // 3. Cari Siswa
    //     $siswa = Siswa::where('nisn', $request->qrcode)->first();

    //     if (!$siswa) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "❌ Data siswa tidak ditemukan!"
    //         ], 404);
    //     }

    //     // 4. Cek Status Siswa
    //     if ($siswa->status != 'aktif') {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "❌ Status siswa tidak aktif."
    //         ]);
    //     }

    //     // --- PROSES ABSENSI SISWA ---

    //     // PENTING: Kirim parameter dengan urutan yang benar
    //     // Kita hanya butuh $siswa, dan $now. Tanggal/Jam biar diurus di dalam fungsi.
    //     $result = $this->logikaAbsensiSiswa($siswa, $now);

    //     // --- KIRIM WA ---
    //     $waStatus = "";
    //     if (!empty($result['pesan_wa']) && !empty($siswa->no_wa)) {
    //         $isSent = WhatsappService::send($siswa->no_wa, $result['pesan_wa']);
    //         $waStatus = $isSent ? "📲 WA terkirim." : "⚠️ Gagal kirim WA.";
    //     }

    //     // Return JSON ke Tampilan Scanner
    //     return response()->json([
    //         "status"    => "success",
    //         "message"   => $result['msg'] . "<br><small>$waStatus</small>",
    //         "data"      => [
    //             "nama" => $siswa->nama,
    //             "jam"  => $now->format('H:i:s'),
    //             "type" => $result['status'] // masuk / pulang
    //         ]
    //     ]);
    // }

    // =========================================================================
    // ABSENSI GURU (LOKASI / GPS HP)
    // =========================================================================
    public function storeGuru(Request $request)
    {
        // Set Waktu
        $now = Carbon::now('Asia/Jakarta');

        // 1. HAPUS 'guru_id' DARI VALIDASI
        $request->validate([
            'latitude'  => 'required',
            'longitude' => 'required',
        ]);

        // Ambil data guru dari user yang sedang login
        $user = Auth::user();

        // Pastikan user ini punya relasi ke tabel guru (jika struktur table user & guru terpisah)
        // Jika auth user langsung tabel guru, gunakan $guru = $user;
        $guru = $user->guru ?? $user;

        // Safety check jika data guru tidak ditemukan
        if (!$guru) {
            return redirect()->back()->with('error', 'Data profil guru tidak ditemukan.');
        }

        $profil = ProfilSekolah::first();

        // --- CEK RADIUS LOKASI ---
        if ($profil && $profil->latitude && $profil->longitude) {
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $profil->latitude,
                $profil->longitude
            );

            $maxRadius = $profil->radius ? $profil->radius : 100; // Radius dalam meter

            if ($jarak > $maxRadius) {
                // 2. JANGAN PAKAI JSON RESPONSE UNTUK FORM BIASA
                // Gunakan redirect back agar pesan muncul di halaman blade
                return redirect()->back()->with('error', "❌ Posisi terlalu jauh (" . round($jarak) . "m). Max: $maxRadius m.");
            }
        }

        // --- PROSES ABSENSI GURU ---
        // Kirim object $guru yang sudah didapat dari Auth
        $result = $this->logikaAbsensiGuru($guru, $now, $request->latitude, $request->longitude);

        // --- KIRIM WA ---
        if (!empty($result['pesan_wa']) && !empty($guru->no_wa)) {
            // Pastikan WhatsappService sudah di import
            WhatsappService::send($guru->no_wa, $result['pesan_wa']);
            WhatsappService::send($profil->wakur_wa, $result['pesan_wa']); //Khusus untuk kirim ke BK
        }

        // Cek hasil status dari logikaAbsensiGuru
        if ($result['status'] == 'error') {
            return redirect()->back()->with('error', strip_tags($result['msg']));
        }

        return redirect()->back()->with('success', strip_tags($result['msg']));
    }

    // =========================================================================
    // PRIVATE LOGIC (REUSABLE & FIXED)
    // =========================================================================
    /**
     * Logika Absen Siswa
     */
    private function logikaAbsensiSiswa($siswa, $nowInput)
    {
        // 1. Pastikan $now adalah Carbon yang valid
        $now = Carbon::parse($nowInput)->setTimezone('Asia/Jakarta');

        // 2. Ambil "Tanggal Bersih" (Y-m-d) untuk query DB & pembentukan jam batas
        //    Ini mencegah error "Double time specification"
        $tanggalBersih = $now->format('Y-m-d');

        $profil = ProfilSekolah::first();

        // 3. Ambil Jadwal (Pakai optional agar tidak error jika siswa belum masuk kelas)
        $jamMasukMax = optional($siswa->kelas)->jam ?? optional($profil)->jam_masuk_guru ?? '07:15:00';
        $jamPulangMin = optional($siswa->kelas)->jam_pulang ?? optional($profil)->jam_pulang_guru ?? '13:00:00';

        // 4. Cek Data Hari Ini
        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggalBersih)
            ->first();

        if (!$absensi) {
            // ===========================
            // --- ABSEN MASUK ---
            // ===========================

            // Gabungkan Tanggal Bersih + Jam Masuk untuk perbandingan
            // Format string: "2024-02-05 07:15:00"
            $batasMasuk = Carbon::parse($tanggalBersih . ' ' . $jamMasukMax);

            // Cek Keterlambatan
            $isTerlambat = $now->gt($batasMasuk);

            $keterangan = $isTerlambat ? 'Terlambat' : 'Tepat Waktu';

            Absensi::create([
                'siswa_id'    => $siswa->id,
                'tanggal'   => $tanggalBersih,
                'jam_masuk' => $now,
                'status'      => 'H',
                'keterangan'  => $keterangan
            ]);

            $namaSekolah = optional($profil)->nama_sekolah ?? 'Sekolah';
            $pesanWa = "Info Absen:\nAnanda *$siswa->nama* ($siswa->nisn)\ntelah *ABSEN MASUK*" . ($isTerlambat ? ' (TERLAMBAT)' : '') . "\npada " . $now->format('d-m-Y H:i') . ".\n\n> " . $namaSekolah;

            $badgeColor = $isTerlambat ? 'alert-warning' : 'alert-success';
            return [
                'status' => 'masuk',
                'msg' => "<div class='alert $badgeColor'>✅ Absen MASUK Berhasil.<br>Status: <b>$keterangan</b></div>",
                'pesan_wa' => $pesanWa
            ];
        } else {
            // ===========================
            // --- ABSEN PULANG ---
            // ===========================

            if (empty($absensi->jam_pulang)) {

                // Gabungkan Tanggal Bersih + Jam Pulang
                $batasPulang = Carbon::parse($tanggalBersih . ' ' . $jamPulangMin);

                // Cek apakah sudah boleh pulang
                if ($now->lt($batasPulang)) {
                    return [
                        'status' => 'error',
                        'msg' => "<div class='alert alert-danger'>⛔ Belum jam pulang! (Min: $jamPulangMin)</div>",
                        'pesan_wa' => null
                    ];
                }

                $absensi->update([
                    'jam_pulang' => $now, // Pastikan kolom DB bernama 'jam_pulang'
                ]);

                $namaSekolah = optional($profil)->nama_sekolah ?? 'Sekolah';
                $pesanWa = "Info Absen:\nAnanda *$siswa->nama* ($siswa->nisn)\ntelah *ABSEN PULANG*\npada " . $now->format('d-m-Y H:i') . ".\n\n> " . $namaSekolah;

                return [
                    'status' => 'pulang',
                    'msg' => "<div class='alert alert-success'>👋 Absen PULANG Berhasil. Hati-hati di jalan!</div>",
                    'pesan_wa' => $pesanWa
                ];
            } else {
                return [
                    'status' => 'info',
                    'msg' => "<div class='alert alert-info'>ℹ️ Siswa ini sudah menyelesaikan absen hari ini.</div>",
                    'pesan_wa' => null
                ];
            }
        }
    }

    /**
     * Logika Absen Guru
     */
    private function logikaAbsensiGuru($guru, $nowInput, $lat, $long)
    {
        $now = Carbon::parse($nowInput)->setTimezone('Asia/Jakarta');

        $tanggalBersih = $now->format('Y-m-d');

        $profil = ProfilSekolah::first();
        $jamMasukMax = optional($profil)->jam_masuk_guru ?? '07:00:00';
        $jamPulangMin = optional($profil)->jam_pulang_guru ?? '14:00:00';

        $absensi = Absensi::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggalBersih)
            ->first();

        if (!$absensi) {
            // --- GURU MASUK ---
            $batasMasuk = Carbon::parse($tanggalBersih . ' ' . $jamMasukMax);

            $isTerlambat = $now->gt($batasMasuk);
            $keterangan = $isTerlambat ? 'Terlambat' : 'Tepat Waktu';

            Absensi::create([
                'guru_id'     => $guru->id,
                'tanggal'   => $tanggalBersih,
                'jam_masuk' => $now,
                'status'      => 'H',
                'latitude'    => $lat,
                'longitude'   => $long,
                'keterangan'  => $keterangan
            ]);

            $pesanWa = "Presensi: Yth. *$guru->nama*, Anda telah *ABSEN MASUK* pada " . $now->format('H:i') . ".";

            return ['status' => 'masuk', 'msg' => "Absen Masuk Berhasil ($keterangan)", 'pesan_wa' => $pesanWa];
        } else {
            // --- GURU PULANG ---
            if (empty($absensi->jam_pulang)) {

                $batasPulang = Carbon::parse($tanggalBersih . ' ' . $jamPulangMin);

                if ($now->lt($batasPulang)) {
                    return ['status' => 'error', 'msg' => "Belum waktunya pulang. (Jadwal: $jamPulangMin)", 'pesan_wa' => null];
                }

                $absensi->update([
                    'jam_pulang' => $now,
                ]);

                $pesanWa = "Presensi: Yth. *$guru->nama*, Anda telah *ABSEN PULANG* pada " . $now->format('H:i') . ".";

                return ['status' => 'pulang', 'msg' => "Absen Pulang Berhasil.", 'pesan_wa' => $pesanWa];
            } else {
                return ['status' => 'info', 'msg' => "Anda sudah absen pulang hari ini.", 'pesan_wa' => null];
            }
        }
    }

    // Fungsi Hitung Jarak (Haversine Formula) dalam Meter
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
