# 📚 Sistem Absensi Sekolah Berbasis RFID (V2)

Sistem manajemen kehadiran real-time untuk siswa dan guru menggunakan kartu RFID, dibangun dengan Laravel. Dilengkapi dashboard statistik, rekapitulasi kehadiran, dan portal khusus siswa/wali murid.

![Status Project](https://img.shields.io/badge/Status-Active-success)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Fitur Utama

### 👥 Multi-Role Authentication
- **Admin**: Manajemen pengguna, kelas, dan laporan lengkap
- **Guru**: Pantau kehadiran siswa, input izin/sakit
- **Siswa**: Lihat riwayat kehadiran pribadi

### 📊 Dashboard Real-time
- Grafik tren kehadiran 7 hari terakhir (Chart.js)
- Pie chart komposisi kehadiran (Hadir, Sakit, Izin, Alpha)
- Log aktivitas terbaru (tap terakhir)
- Statistik harian/bulanan

### 🎫 Sistem Absensi RFID
- Tap kartu RFID untuk catat masuk/pulang
- Validasi: cegah double tap masuk
- Notifikasi: status kehadiran (tepat waktu, telat)
- Support USB/Serial RFID Reader

### 📈 Portal Siswa
- Riwayat absensi per bulan dengan filter
- Ringkasan statistik kehadiran pribadi
- Export data kehadiran (PDF/Excel)

### 📋 Rekapitulasi & Laporan
- Laporan harian/bulanan per kelas
- Export ke Excel/PDF
- Filter berdasarkan periode, kelas, status

## 🏗️ Arsitektur Sistem

```
Backend: Laravel 10.x
Database: MySQL 8.x
Frontend: Blade Templates, Bootstrap 5
Charts: Chart.js
Build Tool: Vite
```

## 🚀 Instalasi & Konfigurasi

### Prasyarat
- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 5.7+
- Git

### 📥 Instalasi (Development)

1. **Clone Repository**
```bash
git clone https://github.com/JeffriAlpian/Absensi_Digital_v2.git
cd Absensi_Digital_v2
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Konfigurasi Environment**
```bash
cp .env.example .env
# Edit file .env sesuai konfigurasi database Anda
```

4. **Generate Key & Setup Database**
```bash
php artisan key:generate
php artisan migrate --seed
```

5. **Compile Assets**
```bash
npm run build
# Untuk development: npm run dev
```

6. **Jalankan Server**
```bash
php artisan serve
# Akses: http://localhost:8000 atau http://127.0.0.1:8000
```

### 🔐 Akun Default
Setelah migrasi dengan seed:
- **Admin**: admin / password123

## ☁️ Deployment ke Shared Hosting (cPanel)

### 📦 1. Persiapan File (Membuat Zip)
Sebelum mengupload ke hosting, pastikan Anda telah mem-build asset frontend (jika ada perubahan) di server lokal dengan perintah `npm run build`.

**Daftar yang perlu di-zip:**
Pilih (blok) seluruh file dan folder di dalam proyek Anda **KECUALI**:
- ❌ `node_modules/` (ukurannya besar dan tidak diperlukan di production)
- ❌ `.git/` (folder riwayat versi/git)
- ❌ `tests/` (opsional, karena tidak dijalankan di production)

Setelah diblok, jadikan satu file arsip, misalnya `app.zip`. Pastikan Anda men-zip *isi* foldernya, bukan folder utamanya, agar saat diekstrak tidak ada folder ganda.

---

### 🚀 2. Metode Konfigurasi di cPanel
Ada dua metode yang bisa digunakan. Pilih salah satu yang didukung oleh layanan hosting Anda.

#### 🌟 Metode A: Mengubah Document Root (Lebih Aman & Direkomendasikan)
Metode ini sangat disarankan jika Anda menggunakan **Subdomain** atau hosting Anda mengizinkan perubahan Document Root untuk domain utama.

1. Buka **File Manager** cPanel, buat folder baru di luar `public_html` (misal: `absensi_app`).
2. Upload dan **Extract** `app.zip` ke dalam folder `absensi_app` tersebut.
3. Buka menu **Domains** atau **Subdomains** di cPanel.
4. Edit **Document Root** dari domain/subdomain Anda agar mengarah ke folder public Laravel.
   *Contoh: ubah `/public_html/absensi` menjadi `/absensi_app/public`*.
5. Buat database di **MySQL® Databases** beserta User-nya.
6. Edit file `.env` di dalam folder `absensi_app`, sesuaikan kredensial:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   DB_DATABASE=nama_database
   DB_USERNAME=user_database
   DB_PASSWORD=password_database
   ```
7. Selesai! Aplikasi Anda sudah bisa diakses dengan aman.

---

#### 📁 Metode B: Memisahkan Folder Public (Untuk Domain Utama)
Gunakan metode ini jika Anda memakai domain utama dan cPanel **TIDAK MENGIZINKAN** pengubahan Document Root dari `public_html`.

**Struktur Folder Target:**
```text
/home/username/
├── laravel_core/         # Buat folder ini untuk menyimpan file inti (private)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── vendor/
│   └── ... (file lainnya)
└── public_html/          # Folder Public bawaan cPanel
    ├── index.php         # File index.php yang sudah dimodifikasi
    ├── build/            # Asset hasil build Vite
    └── storage           # Symlink ke storage/app/public
```

**Langkah-langkah:**
1. Buka **File Manager**, buat folder `laravel_core` sejajar dengan `public_html`.
2. Upload dan **Extract** `app.zip` ke dalam folder `laravel_core`.
3. Masuk ke folder `laravel_core/public`, lalu **pindahkan (move)** seluruh isinya ke dalam folder `public_html`.
4. Buka folder `public_html`, lalu edit file `index.php`.
5. Modifikasi dua baris berikut agar mengarah ke folder `laravel_core`:
   ```php
   // Ubah dari: require __DIR__.'/../vendor/autoload.php';
   // Menjadi:
   require __DIR__.'/../laravel_core/vendor/autoload.php';

   // Ubah dari: $app = require_once __DIR__.'/../bootstrap/app.php';
   // Menjadi:
   $app = require_once __DIR__.'/../laravel_core/bootstrap/app.php';
   ```
6. Sesuaikan konfigurasi database dan environment di file `.env` yang berada di dalam `laravel_core`.

---

### 🔗 3. Setup Storage Link (Menampilkan Gambar/File)
Agar gambar profil atau file lain bisa diakses publik, buat symbolic link storage.
Jika tidak bisa menggunakan command line / SSH, Anda bisa membuat symlink menggunakan route atau cron job.

**Alternatif: Route Symlink (Tambahkan di `routes/web.php` untuk sementara):**
```php
Route::get('/create-symlink', function () {
    $targetFolder = $_SERVER['DOCUMENT_ROOT'].'/../laravel_core/storage/app/public';
    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/storage';
    symlink($targetFolder, $linkFolder);
    return 'Symlink created successfully';
});
```
*Akses `https://domain-anda.com/create-symlink`, lalu hapus kembali route tersebut setelah berhasil.*

### 🔑 4. Permission Folder
Pastikan direktori ini memiliki permission `775` (atau dapat ditulis):
- `/storage` dan subfoldernya
- `/bootstrap/cache`

## 🔧 Integrasi RFID Reader

### Konfigurasi Hardware
1. Hubungkan RFID Reader via USB
2. Reader akan terdeteksi sebagai keyboard/COM port
3. Format data: [UID_KARTU][ENTER]

### Testing Reader
```javascript
// Contoh script testing di browser
document.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') {
        let uid = document.getElementById('rfid-input').value;
        console.log('RFID UID:', uid);
        // Kirim ke API /api/attendance/tap
    }
});
```

## 📊 API Endpoints

### Absensi
```
POST   /api/rfid/catat      # Tap kartu RFID
GET    /api/attendance/today    # Data kehadiran hari ini
GET    /api/attendance/monthly  # Data bulanan
```

### Laporan
```
GET    /api/reports/daily       # Laporan harian
GET    /api/reports/monthly     # Laporan bulanan
POST   /api/reports/export      # Export data
```

## 🐛 Troubleshooting

### Error 500 After Deployment
1. **Cek error log**: `laravel_app/storage/logs/laravel.log`
2. **Permission**: Pastikan folder storage writable
3. **PHP Extension**: Pastikan ekstensi berikut aktif:
   - fileinfo
   - mbstring
   - pdo_mysql
   - openssl
   - tokenizer
   - xml
   - zip
   - imagick

### Vite Manifest Not Found
```bash
# Jalankan di local sebelum upload
npm run build
# Upload folder `public/build` ke `public_html/build`
```

### Symlink Tidak Bekerja
1. Alternatif 1: Upload manual ke `public_html/storage`
2. Alternatif 2: Ubah konfigurasi filesystem (lihat bagian Deployment)
3. Alternatif 3: Gunakan plugin cPanel "File Manager" → "Create Symbolic Link"

### Database Connection Error
1. Pastikan kredensial di `.env` benar
2. Cek MySQL remote connection di cPanel
3. Pastikan prefix database benar (biasanya `username_`)

## 🔐 Keamanan

### Best Practices
1. **Ganti password default** setelah instalasi
2. **Enable HTTPS** di cPanel
3. **Restrict .env access**:
```nginx
location ~ /\.env {
    deny all;
}
```
4. **Update Laravel** secara berkala

### Backup Routine
```bash
# Backup database (cron job)
mysqldump -u username -p database > backup-$(date +%Y%m%d).sql
# Backup file
tar -czf backup-$(date +%Y%m%d).tar.gz /home/username/laravel_app
```

## 📝 Changelog

### v2.0.0 (Current)
- [x] Multi-role authentication
- [x] Real-time dashboard dengan Chart.js
- [x] Portal siswa dengan riwayat pribadi
- [x] Export laporan Excel/PDF
- [x] Optimasi untuk shared hosting

### v1.0.0
- Basic RFID attendance system
- Simple admin panel
- Daily reports

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## 👥 Kontak

Nama Project - Sistem Absensi RFID Sekolah  
Developer Team - [tim@email.com](mailto:tim@email.com)  
Project Link: [https://github.com/JeffriAlpian/Absensi_Digital_v2.git](https://github.com/JeffriAlpian/Absensi_Digital_v2.git)

---.