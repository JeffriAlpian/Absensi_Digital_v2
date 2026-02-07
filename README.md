# 🏫 Sistem Absensi Sekolah Berbasis RFID (V2)

Sistem manajemen kehadiran siswa dan guru real-time menggunakan kartu RFID, dibangun dengan Laravel. Aplikasi ini mencakup dashboard statistik, rekapitulasi kehadiran, dan portal khusus siswa/wali murid.

![Status Project](https://img.shields.io/badge/Status-Active-success)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)

## 🌟 Fitur Utama

* **Multi-Role Auth:** Admin, Guru, dan Siswa.
* **Real-time Attendance:** Tap kartu RFID untuk Masuk/Pulang.
* **Dashboard Statistik:**
    * Grafik tren kehadiran 7 hari terakhir.
    * Pie chart komposisi kehadiran (Hadir, Sakit, Izin, Alpha).
    * Log aktivitas terbaru.
* **Portal Siswa:**
    * Melihat riwayat absensi pribadi per bulan.
    * Ringkasan statistik kehadiran siswa.
* **Rekapitulasi:** Export laporan kehadiran harian/bulanan.

## 🛠️ Teknologi yang Digunakan

* **Backend:** Laravel Framework
* **Database:** MySQL
* **Frontend:** Blade Templates, Chart.js (untuk grafik)
* **Build Tool:** Vite (untuk compile asset CSS/JS)
* **Hardware Support:** Support input RFID Reader (via USB/Serial emulation)

---

## 💻 Instalasi Lokal (Development)

Ikuti langkah ini untuk menjalankan project di komputer lokal (Laptop/PC):

1.  **Clone Repositori**
    ```bash
    git clone [https://github.com/username-anda/nama-repo.git](https://github.com/username-anda/nama-repo.git)
    cd nama-repo
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    * Duplikat file `.env.example` menjadi `.env`
    * Atur koneksi database:
        ```env
        DB_DATABASE=nama_database_anda
        DB_USERNAME=root
        DB_PASSWORD=
        ```

4.  **Generate Key & Migrasi**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```
    *(Gunakan `--seed` jika ada data dummy)*

5.  **Jalankan Project**
    * Terminal 1: `php artisan serve`
    * Terminal 2: `npm run dev`

---

## 🚀 Panduan Deployment ke cPanel (Shared Hosting)

Karena menggunakan Shared Hosting, struktur folder harus dipisah antara **Core** (Logic) dan **Public** (Asset).

### 1. Persiapan File
1.  Jalankan `npm run build` di local untuk compile asset.
2.  Compress (Zip) seluruh project (kecuali `node_modules`).

### 2. Struktur Folder di Hosting
Buat struktur seperti ini agar aman:
* `/home/user/laravel_app` (Folder Core - berisi app, config, storage, vendor, dll)
* `/home/user/public_html` (Folder Public - berisi index.php, build, assets)

### 3. Konfigurasi `index.php`
Edit file `public_html/index.php`. Sesuaikan path agar mengarah ke folder Core:

```php
// Sesuaikan path ke folder core
if (file_exists($maintenance = __DIR__.'/../laravel_app/storage/framework/maintenance.php')) {
    require $maintenance;
}
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

// PENTING: Set public path
$app->usePublicPath(__DIR__);

4. Mengatasi Masalah Symlink (Storage)
Jika fitur symlink PHP dimatikan hosting, gunakan Cron Job:

Masuk menu Cron Jobs di cPanel.

Buat cron baru (Once per minute).

Command:

Bash
ln -s /home/username/laravel_app/storage/app/public /home/username/public_html/storage
Tunggu 1 menit, cek apakah folder storage muncul di public_html.

Hapus Cron Job tersebut segera setelah berhasil.

5. Mengatasi "Vite Manifest Not Found"
Pastikan folder build hasil npm run build di local sudah di-upload ke dalam public_html. Struktur harus: public_html/build/manifest.json.

🐛 Troubleshooting Umum
Q: Error 500 Server Error

Cek permission folder storage dan bootstrap/cache di Core. Ubah ke 755 atau 775.

Pastikan path di index.php sudah benar (jumlah ../ nya pas).

Pastikan ekstensi PHP fileinfo, pdo, mbstring aktif di cPanel.

Q: Gambar tidak muncul

Hapus folder storage di public_html.

Jalankan ulang trik Symlink via Cron Job di atas.