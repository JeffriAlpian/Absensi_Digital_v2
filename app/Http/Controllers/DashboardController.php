<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Cek Role User
        if ($user->role === 'guru') {
            return $this->dashboardGuru($request);
        } elseif ($user->role === 'siswa') {
            return $this->dashboardSiswa($request);
        }

        $today = Carbon::now()->format('Y-m-d');

        // --- 1. STATISTIK UTAMA (CARDS) ---
        $stats = [
            'total_siswa'   => Siswa::where('status', 'aktif')->count(),
            'total_guru'    => Guru::where('status', 'aktif')->count(),
            'hadir_hari_ini' => Absensi::whereDate('tanggal', $today)->where('status', 'H')->count(),
            'alpha_hari_ini' => Absensi::whereDate('tanggal', $today)->where('status', 'A')->count(),
        ];

        // --- 2. DATA UNTUK CHART TREN (7 HARI TERAKHIR) ---
        // Kita butuh array tanggal dan array jumlah hadir
        $chartLabels = [];
        $chartDataHadir = [];
        $chartDataTelat = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $chartLabels[] = $date->format('d M'); // Label: "06 Feb"

            // Hitung Hadir pada tanggal tersebut
            $chartDataHadir[] = Absensi::whereDate('tanggal', $dateStr)
                ->where('status', 'H')
                ->count();

            // Hitung Terlambat (Asumsi 'Terlambat' ada di keterangan)
            $chartDataTelat[] = Absensi::whereDate('tanggal', $dateStr)
                ->where('keterangan', 'LIKE', '%Terlambat%')
                ->count();
        }

        // --- 3. DATA PIE CHART (KOMPOSISI HARI INI) ---
        $pieData = [
            'H' => Absensi::whereDate('tanggal', $today)->where('status', 'H')->count(),
            'S' => Absensi::whereDate('tanggal', $today)->where('status', 'S')->count(),
            'I' => Absensi::whereDate('tanggal', $today)->where('status', 'I')->count(),
            'A' => Absensi::whereDate('tanggal', $today)->where('status', 'A')->count(),
        ];

        // --- 4. AKTIVITAS TERBARU (5 TERAKHIR) ---
        $latestActivities = Absensi::with(['siswa', 'guru'])
            ->whereDate('tanggal', $today)
            ->orderBy('updated_at', 'desc') // Yang baru update (masuk/pulang)
            ->limit(5)
            ->get();

        // 1. Hitung Jumlah Antrean
        $pendingCount = DB::table('jobs')->count();
        $failedCount = DB::table('failed_jobs')->count();

        // 2. Ambil List Antrean (Limit 5 saja agar tidak berat)
        $pendingJobs = DB::table('jobs')
            ->orderBy('id', 'asc') // Yang paling lama mengantre di atas
            ->limit(5)
            ->get();

        // 3. Ambil List Gagal
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'chartLabels',
            'chartDataHadir',
            'chartDataTelat',
            'pieData',
            'latestActivities',
            'pendingCount',
            'failedCount',
            'pendingJobs',
            'failedJobs'
        ));
    }

    // Fitur Tambahan: Retry Semua yang Gagal
    public function retryAllWA()
    {
        Artisan::call('queue:retry all');
        return back()->with('success', 'Semua antrean gagal sedang diproses ulang.');
    }

    // Fitur Tambahan: Hapus yang Gagal (Bersihkan)
    public function flushFailedWA()
    {
        Artisan::call('queue:flush');
        return back()->with('success', 'Semua log gagal telah dihapus.');
    }

    // 2. Logic Khusus Halaman Guru
    public function dashboardGuru(Request $request)
    {
        $user = Auth::user();

        // Ambil data detail guru berdasarkan user_id (Asumsi ada relasi)
        // Jika tabel user digabung dengan data guru, cukup pakai $user
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
             // 1. Logout user secara manual
            Auth::logout();

            // 2. Bersihkan sesi (Invalidate Session)
            $request->session()->invalidate();

            // 3. Regenerate Token (Keamanan)
            $request->session()->regenerateToken();

            // 4. Redirect ke halaman login dengan pesan error
            return redirect('/login')->with('error', 'Data siswa tidak ditemukan. Hubungi Admin.');
        }

        // 1. Ambil SEMUA data absensi bulan ini ke dalam variabel (Collection)
        $dataAbsensiBulanIni = Absensi::where('guru_id', $guru->id)
            ->whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->get(); // Perhatikan: Kita pakai get() bukan first()

        // 2. Hitung menggunakan Collection Helper Laravel
        // Ini berjalan di RAM server, bukan di Database SQL
        $totalHadir = $dataAbsensiBulanIni->where('status', 'H')->count();
        $totalIzin  = $dataAbsensiBulanIni->where('status', 'I')->count();
        $totalSakit = $dataAbsensiBulanIni->where('status', 'S')->count();
        $totalAlpha = $dataAbsensiBulanIni->where('status', 'A')->count();

        // Untuk terlambat, kita cek string di kolom keterangan (case sensitive sensitive helper)
        // filter() digunakan untuk logika custom
        $totalTelat = $dataAbsensiBulanIni->filter(function ($item) {
            return str_contains($item->keterangan, 'Terlambat');
        })->count();

        // 3. Ambil data hari ini (sama seperti sebelumnya)
        $absenHariIni = Absensi::where('guru_id', $guru->id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        // 4. Kirim ke View
        return view('dashboard.guru.index', compact(
            'guru',
            'absenHariIni',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpha',
            'totalTelat'
        ));
    }

    public function dashboardSiswa(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil Data Siswa beserta relasi Kelas
        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->first();

        if (!$siswa) {
            // 1. Logout user secara manual
            Auth::logout();

            // 2. Bersihkan sesi (Invalidate Session)
            $request->session()->invalidate();

            // 3. Regenerate Token (Keamanan)
            $request->session()->regenerateToken();

            // 4. Redirect ke halaman login dengan pesan error
            return redirect('/login')->with('error', 'Data siswa tidak ditemukan. Hubungi Admin.');
        }

        // Variabel pendukung untuk View
        $nisn_siswa = $siswa->nisn ?? $user->username;

        // 2. Ambil Filter Bulan & Tahun dari Request
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));

        // 3. Query History Absensi (Untuk Tabel)
        // Kita gunakan Model Absensi tapi di-select spesifik agar kompatibel dengan view ($row->jam)
        $absensiList = Absensi::where('siswa_id', $siswa->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->select('*', 'jam_masuk')
            ->get();

        // 4. Hitung Ringkasan (H, S, I, A) untuk Kotak Statistik
        // Kita pakai grouping query agar efisien
        $stats = Absensi::where('siswa_id', $siswa->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Gabungkan dengan default 0 (agar tidak error jika kosong)
        $ringkasanBulanIni = array_merge([
            'H' => 0,
            'S' => 0,
            'I' => 0,
            'A' => 0
        ], $stats);

        // Tambahan: Profil sekolah (opsional, set null jika belum ada model Profil)
        $profil = null;

        // Return ke view yang baru Anda buat
        // Pastikan nama filenya sesuai lokasi, misal: resources/views/dashboard/siswa.blade.php
        return view('dashboard.siswa.index', compact(
            'siswa',
            'nisn_siswa',
            'absensiList',
            'ringkasanBulanIni',
            'profil'
        ));
    }
}
