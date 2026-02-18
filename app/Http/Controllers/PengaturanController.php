<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Ifsnop\Mysqldump as IMysqldump; // Import Library Backup
use Codedge\Updater\UpdaterManager;

class PengaturanController extends Controller
{
    // Folder penyimpanan backup (di dalam storage/app)
    private $backupFolder = 'backups/db';

    public function index()
    {
        // 1. DATA PROFIL SEKOLAH
        $profil = ProfilSekolah::first() ?? new ProfilSekolah();

        // 2. DATA SYSTEM INFO
        $system_info = [
            'Laravel Version' => app()->version(),
            'PHP Version' => phpversion(),
            'Database' => DB::connection()->getDatabaseName(),
            'Server OS' => php_uname('s') . ' ' . php_uname('r'),
            'Timezone' => config('app.timezone'),
            'IP Address' => request()->server('SERVER_ADDR') ?? '127.0.0.1',
        ];

        $lat = $profil->latitude ?? -6.175392;
        $long = $profil->longitude ?? 106.827153;
        $radius = $profil->radius ?? 100;

        // 3. DATA BACKUP (Metode Baru: Baca dari folder 'backups/db')
        $backups = [];

        try {
            // Cek folder backup
            if (Storage::exists($this->backupFolder)) {
                $files = Storage::files($this->backupFolder);

                // Filter file .sql atau .zip/.gz
                $files = collect($files)->filter(function ($file) {
                    return preg_match('/\.(sql|zip|gz)$/', $file);
                })->sortByDesc(function ($file) {
                    return Storage::lastModified($file);
                });

                foreach ($files as $file) {
                    $size = Storage::size($file);
                    $timestamp = Storage::lastModified($file);

                    $backups[] = [
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => $this->formatBytes($size),
                        'date' => Carbon::createFromTimestamp($timestamp)->format('d M Y H:i:s'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal membaca folder backup: " . $e->getMessage());
        }

        return view('pengaturan.index', compact('profil', 'system_info', 'backups', 'lat', 'long', 'radius'));
    }

    public function updateProfil(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'logo_sekolah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'desain_kartu_siswa_depan' => 'nullable|image|max:2048',
            'desain_kartu_siswa_belakang' => 'nullable|image|max:2048',
            'desain_kartu_guru_depan' => 'nullable|image|max:2048',
            'desain_kartu_guru_belakang' => 'nullable|image|max:2048',
        ]);

        $profil = ProfilSekolah::first() ?? new ProfilSekolah();

        $profil->nama_sekolah = $request->nama_sekolah;
        $profil->alamat_sekolah = $request->alamat;
        $profil->kepala_sekolah = $request->kepala_sekolah;
        $profil->nip_kepala_sekolah = $request->nip_kepsek;
        $profil->key_wa_sidobe = $request->api_key_wa;
        $profil->jam_masuk_guru = $request->jam_masuk_guru;
        $profil->jam_pulang_guru = $request->jam_pulang_guru;
        $profil->latitude = $request->latitude;
        $profil->longitude = $request->longitude;
        $profil->radius = $request->radius;
        $profil->wakur_wa = $request->wakur_wa;
        $profil->hari_libur_mingguan = $request->hari_libur_mingguan;

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

    public function systemUpdate(Request $request)
    {
        try {
            // 1. Baca versi dari file version.txt di root aplikasi
            $versionFile = base_path('version.txt');
            if (!file_exists($versionFile)) {
                return redirect()->back()->with('error', '❌ File version.txt tidak ditemukan di root aplikasi.');
            }

            $installedVersion = trim(file_get_contents($versionFile));
            if (empty($installedVersion)) {
                return redirect()->back()->with('error', '❌ File version.txt kosong.');
            }

            // 2. Set konfigurasi dan environment variable untuk memastikan terbaca
            config(['self-update.version_installed' => $installedVersion]);
            putenv("SELF_UPDATER_VERSION_INSTALLED={$installedVersion}");

            // 3. Hapus instance lama agar container membuat instance baru dengan konfigurasi terbaru
            app()->forgetInstance(UpdaterManager::class);

            // 4. Buat instance baru dan jalankan pengecekan
            $updater = app(UpdaterManager::class);
            $source = $updater->source();

            if ($source->isNewVersionAvailable()) {
                $newVersion = $source->getVersionAvailable();

                // Unduh dan ekstrak
                $release = $source->fetch($newVersion);
                $source->update($release);

                // Jalankan migrasi dan bersihkan cache
                \Artisan::call('migrate', ['--force' => true]);
                \Artisan::call('optimize:clear');

                return redirect()->back()->with('success', "✅ Update ke versi {$newVersion} berhasil!");
            } else {
                return redirect()->back()->with('error', "ℹ️ Aplikasi sudah menggunakan versi terbaru ({$installedVersion}).");
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Gagal update: ' . $e->getMessage());
        }
    }


    /**
     * Backup Database Menggunakan PHP Native (Tanpa mysqldump shell command)
     * Solusi untuk hosting yang memblokir proc_open
     */
    public function databaseBackup()
    {
        // 1. Setup Limit
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            // 2. Ambil Config DB
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $port = config('database.connections.mysql.port');

            // 3. Tentukan Nama File & Lokasi
            // Contoh: backup-smk-2024-02-12_20-00.sql.gz
            $appName = Str::slug(env('APP_NAME', 'laravel'));
            $fileName = 'backup-' . $appName . '-' . date('Y-m-d_H-i-s') . '.sql.gz';

            // Simpan di storage/app/backups/db
            $storagePath = storage_path('app/private/' . $this->backupFolder);

            // Pastikan folder ada
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $fullPath = $storagePath . '/' . $fileName;

            // 4. Proses Dumping (Compress GZIP agar hemat tempat)
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $dumpSettings = [
                'compress' => IMysqldump\Mysqldump::GZIP, // Output jadi .gz
                'add-drop-table' => true,
            ];

            $dump = new IMysqldump\Mysqldump($dsn, $username, $password, $dumpSettings);
            $dump->start($fullPath);

            // 5. Cek Hasil
            if (file_exists($fullPath)) {
                // Return download langsung ke browser
                return response()->download($fullPath);

                // Opsi lain: Redirect back dengan pesan sukses (file tersimpan di server)
                // return redirect()->back()->with('success', 'Backup berhasil dibuat: ' . $fileName);
            } else {
                throw new \Exception("File backup gagal ditulis ke disk.");
            }

        } catch (\Exception $e) {
            Log::error('Backup Error (PHP Native): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal backup: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        // Path file
        $path = $this->backupFolder . '/' . $filename;

        // Validasi keberadaan file
        if (!Storage::exists($path)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan.');
        }

        return Storage::download($path);
    }

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