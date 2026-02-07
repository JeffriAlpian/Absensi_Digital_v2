<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Cek Role User
        if ($user->role === 'guru') {
            return $this->dashboardGuru();
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

        return view('dashboard.index', compact(
            'stats',
            'chartLabels',
            'chartDataHadir',
            'chartDataTelat',
            'pieData',
            'latestActivities'
        ));
    }

    // 2. Logic Khusus Halaman Guru
    public function dashboardGuru()
    {
        $user = Auth::user();

        // Ambil data detail guru berdasarkan user_id (Asumsi ada relasi)
        // Jika tabel user digabung dengan data guru, cukup pakai $user
        $guru = Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            // Jika data guru tidak ditemukan, redirect atau tampilkan error
            return redirect()->route('logout')->with('error', 'Data guru tidak ditemukan. Silakan hubungi admin.');
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
}
