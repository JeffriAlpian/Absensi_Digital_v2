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
cd sistem-absensi-rfid
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

### 📁 Struktur Folder
```
/home/username/
├── laravel_app/          # Folder Core (private)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   └── vendor/
└── public_html/          # Folder Public
    ├── index.php         # Modified index.php
    ├── build/            # Compiled assets
    └── storage -> ../laravel_app/storage/app/public  # Symlink
```

### 📋 Langkah Deployment

1. **Upload File Core**
   - Upload seluruh isi project (kecuali `node_modules`, `public/build`) ke `laravel_app`

2. **Upload File Public**
   - Upload isi folder `public` ke `public_html`
   - Pastikan file `index.php` sudah dimodifikasi (lihat bagian berikut)

3. **Konfigurasi `index.php`**
   Edit `public_html/index.php`:
```php
<?php
// Arahkan ke folder core Laravel
$corePath = __DIR__ . '/../laravel_app';

require $corePath . '/vendor/autoload.php';
$app = require_once $corePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);
```

4. **Konfigurasi `.env`**
   Edit `.env` di folder `laravel_app`:
```env
APP_URL=https://domain-anda.com
APP_ENV=production

# Koneksi Database
DB_HOST=localhost
DB_DATABASE=nama_database
DB_USERNAME=username_db
DB_PASSWORD=password_db

# Asset URL
ASSET_URL=https://domain-anda.com
```

5. **Setup Symlink Storage**
   Jika symlink tidak bekerja, gunakan cron job:
   ```bash
   ln -s /home/username/laravel_app/storage/app/public /home/username/public_html/storage
   ```
   
   Atau buat folder manual:
   - Buat folder `public_html/storage`
   - Ubah path di `laravel_app/config/filesystems.php`:
   ```php
   'public' => [
       'driver' => 'local',
       'root' => '/home/username/public_html/storage',
       'url' => env('APP_URL').'/storage',
       'visibility' => 'public',
   ],
   ```

6. **Permission Folder**
```bash
chmod -R 755 /home/username/laravel_app/storage
chmod -R 755 /home/username/laravel_app/bootstrap/cache
```

7. **Optimasi**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

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
POST   /api/attendance/tap      # Tap kartu RFID
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