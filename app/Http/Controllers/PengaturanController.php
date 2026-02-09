<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Tambahan penting untuk Str::slug
use Carbon\Carbon; // Tambahan untuk format tanggal

class PengaturanController extends Controller
{
    public function index()
    {
        // 1. DATA PROFIL SEKOLAH
        $profil = ProfilSekolah::first() ?? new ProfilSekolah();

        // 2. DATA SYSTEM INFO
        $system_info = [
            'Laravel Version' => app()->version(),
            'PHP Version'     => phpversion(),
            'Database'        => DB::connection()->getDatabaseName(),
            'Server OS'       => php_uname('s') . ' ' . php_uname('r'),
            'Timezone'        => config('app.timezone'),
            'IP Address'      => request()->server('SERVER_ADDR') ?? '127.0.0.1',
        ];

        $lat = $profil->latitude ?? -6.175392;
        $long = $profil->longitude ?? 106.827153;
        $radius = $profil->radius ?? 100;

        // 3. DATA BACKUP (REAL - Membaca dari folder penyimpanan)
        $backups = [];

        try {
            // Tentukan folder sesuai APP_NAME (Spatie default)
            $appName = env('APP_NAME');
            $directory = Str::slug($appName);

            // Cek apakah folder ada di storage/app/
            if (Storage::disk('local')->exists($directory)) {
                $files = Storage::disk('local')->files($directory);

                // Urutkan file dari yang terbaru (descending)
                $files = collect($files)->filter(function ($file) {
                    return str_ends_with($file, '.zip');
                })->sortByDesc(function ($file) {
                    return Storage::disk('local')->lastModified($file);
                });

                // Format data untuk dikirim ke View
                foreach ($files as $file) {
                    $size = Storage::disk('local')->size($file);
                    $timestamp = Storage::disk('local')->lastModified($file);

                    $backups[] = [
                        'filename' => basename($file), // Nama file saja (backup-xxx.zip)
                        'path'     => $file,           // Path lengkap
                        'size'     => $this->formatBytes($size),
                        'date'     => Carbon::createFromTimestamp($timestamp)->format('d M Y H:i:s'),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Jika error baca folder, biarkan kosong agar tidak crash
            Log::error("Gagal membaca folder backup: " . $e->getMessage());
        }

        return view('pengaturan.index', compact('profil', 'system_info', 'backups', 'lat', 'long', 'radius'));
    }

    public function updateProfil(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'logo_sekolah'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'desain_kartu_siswa_depan'    => 'nullable|image|max:2048',
            'desain_kartu_siswa_belakang' => 'nullable|image|max:2048',
            'desain_kartu_guru_depan'     => 'nullable|image|max:2048',
            'desain_kartu_guru_belakang'  => 'nullable|image|max:2048',
        ]);

        $profil = ProfilSekolah::first() ?? new ProfilSekolah();

        $profil->nama_sekolah   = $request->nama_sekolah;
        $profil->alamat_sekolah = $request->alamat;
        $profil->kepala_sekolah = $request->kepala_sekolah;
        $profil->nip_kepala_sekolah = $request->nip_kepsek;
        $profil->key_wa_sidobe = $request->api_key_wa;
        $profil->latitude = $request->latitude;
        $profil->longitude = $request->longitude;
        $profil->radius = $request->radius;
        $profil->wakur_wa = $request->wakur_wa;
        $profil->hari_libur_mingguan = $request->hari_libur_mingguan;

        // Helper function (Local Function)
        $uploadFoto = function ($field, $folder) use ($request, $profil) {
            if ($request->hasFile($field)) {
                if ($profil->$field && Storage::disk('public')->exists($profil->$field)) {
                    Storage::disk('public')->delete($profil->$field);
                }
                $profil->$field = $request->file($field)->store($folder, 'public');
            }
        };

        $uploadFoto('logo_sekolah', 'logo_sekolah');
        $uploadFoto('desain_kartu_siswa_depan', 'desain_kartu/siswa');
        $uploadFoto('desain_kartu_siswa_belakang', 'desain_kartu/siswa');
        $uploadFoto('desain_kartu_guru_depan', 'desain_kartu/guru');
        $uploadFoto('desain_kartu_guru_belakang', 'desain_kartu/guru');

        $profil->save();

        return redirect()->back()->with('success', 'Profil sekolah berhasil diperbarui.');
    }

    public function systemUpdate()
    {

        try {
            // 1. Tentukan Path Project
            $path = base_path();

            // 2. Tentukan Command Git untuk Windows
            // Kadang 'git' saja tidak dikenali oleh PHP shell_exec di Windows
            // Coba pakai 'git' dulu, kalau gagal pakai path lengkap default Laragon/Windows
            $gitBin = 'git';
            // Jika gagal, ganti baris atas dengan path asli, contoh:
            // $gitBin = '"C:\Program Files\Git\bin\git.exe"'; 

            // 3. Susun perintah: Masuk folder -> Git Pull -> Simpan Log
            // "2>&1" artinya error message juga akan ditangkap ke variabel $output
            $command = "cd /d \"{$path}\" && {$gitBin} pull origin main 2>&1";

            $output = shell_exec($command);

            // 4. Jalankan perintah maintenance Laravel otomatis
            // Supaya kalau ada tabel baru di database, langsung terbuat
            Artisan::call('migrate --force');
            Artisan::call('optimize:clear'); // Hapus cache view/config lama

            // Gabungkan output untuk laporan
            $finalMessage = "✅ GIT OUTPUT:\n" . $output;
            $finalMessage .= "\n\n✅ ARTISAN OUTPUT:\nMigrate & Optimize Cleared.";

            return redirect()->back()->with('message', $finalMessage);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', '❌ Gagal update: ' . $e->getMessage());
        }
    }

    // public function databaseBackup()
    // {
    //     set_time_limit(0); // Unlimited time execution

    //     try {
    //         // 1. Jalankan Backup (Hanya Database)
    //         // Hasil backup disimpan di: storage/app/absen-rfid-v2/
    //         Artisan::call('backup:run --only-db --disable-notifications');

    //         // 2. Tentukan Folder
    //         $appName = config('app.name');
    //         $directory = Str::slug($appName);

    //         // 3. Cari File Terbaru
    //         if (!Storage::disk('local')->exists($directory)) {
    //             return redirect()->back()->with('warning', "Backup berhasil dibuat, tapi folder '$directory' belum terbaca. Coba refresh.");
    //         }

    //         $files = Storage::disk('local')->files($directory);

    //         if (count($files) > 0) {
    //             // Filter cari file .zip saja dan ambil yang terakhir dimodifikasi
    //             $latestFile = collect($files)->filter(function ($file) {
    //                 return str_ends_with($file, '.zip');
    //             })->sortBy(function ($file) {
    //                 return Storage::disk('local')->lastModified($file);
    //             })->last();

    //             if ($latestFile) {
    //                 // 4. Download file TANPA menghapusnya
    //                 return Storage::download($latestFile);
    //             }
    //         }

    //         return redirect()->back()->with('warning', 'Backup berhasil diproses di server, tapi file zip tidak ditemukan untuk didownload otomatis.');
    //     } catch (\Exception $e) {
    //         Log::error('Backup Error: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Backup Gagal: ' . $e->getMessage());
    //     }
    // }

    public function databaseBackup()
    {
        set_time_limit(0);

        try {
            // 1. Pastikan config dump path sudah benar (lihat poin 1)

            // 2. Jalankan Backup
            // Output dari artisan call ini sebenarnya bisa dicek
            $exitCode = Artisan::call('backup:run --only-db --disable-notifications');

            if ($exitCode !== 0) {
                throw new \Exception("Perintah backup gagal dijalankan. Cek Log.");
            }

            // 3. Ambil Nama Aplikasi dari Config (Bukan Env)
            $appName = config('backup.backup.name'); // Ambil langsung dari config package
            $directory = Str::slug($appName);

            $disk = Storage::disk('local'); // Sesuaikan jika config backup.php pakai disk lain

            // 4. Cari File Zip
            if (!$disk->exists($directory)) {
                // Debugging: Cek isi root storage biar tau folder apa yang terbentuk
                Log::info('Folder di storage: ' . json_encode($disk->directories()));
                return redirect()->back()->with('error', "Folder backup '$directory' tidak ditemukan.");
            }

            $files = $disk->files($directory);

            // ... (kode sorting Anda sudah benar) ...
            if (count($files) > 0) {
                // Filter cari file .zip saja dan ambil yang terakhir dimodifikasi
                $latestFile = collect($files)->filter(function ($file) {
                    return str_ends_with($file, '.zip');
                })->sortBy(function ($file) {
                    return Storage::disk('local')->lastModified($file);
                })->last();
                // Tips: Gunakan full path untuk download response yang lebih aman
                if ($latestFile) {
                    return $disk->download($latestFile);
                }
            }
            
            return redirect()->back()->with('warning', 'File backup tidak ditemukan.');
        } catch (\Exception $e) {
            // Log error lengkap untuk debugging
            Log::error('Backup Error: ' . $e->getMessage());
            Log::error('Backup Trace: ' . $e->getTraceAsString());

            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        // 1. Tentukan folder penyimpanan (harus sama persis dengan logic di index)
        $appName = env('APP_NAME');
        $directory = \Illuminate\Support\Str::slug($appName); // Contoh: absen-rfid-v2

        // 2. Gabungkan folder + nama file
        $path = $directory . '/' . $filename;

        // 3. Cek apakah file ada di storage/app/
        if (!Storage::disk('local')->exists($path)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan di server.');
        }

        // 4. Download file
        return Storage::download($path);
    }

    // Helper untuk konversi bytes ke KB/MB (Dipakai di index)
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
