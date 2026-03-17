<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CetakKartuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceRfidController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\KartuController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\WakelController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index')->middleware('auth');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware('auth');

Route::middleware(['auth', 'role:admin,guru'])->group(function () {

    Route::post('/absensi/manual/store', [AbsensiController::class, 'storeManual'])->name('absensi.manual.storeManual');
    Route::post('/absensi/manual/storeAll', [AbsensiController::class, 'storeAll'])->name('absensi.manual.storeAll');

    Route::get('/dashboardGuru', [DashboardController::class, 'index'])->name('dashboard.guru.index');
    Route::get('/dashboardGuru/scan', [GuruController::class, 'indexGuruScan'])->name('dashboard.guru.scan.index');

    Route::post('/scan/storeScan', [AbsensiController::class, 'storeScan'])->name('scan.storeScan');

    // Route spesifik menu guru
    Route::get('/dashboardGuru/absen', [GuruController::class, 'indexGuruabsen'])->name('dashboard.guru.absen.index');
    Route::post('/dashboardGuru/absen/store', [AbsensiController::class, 'storeGuru'])->name('dashboard.guru.absen.store');

    Route::get('/dashboardGuru/profil', [GuruController::class, 'indexGuruProfile'])->name('dashboard.guru.profile.index');
    Route::put('/dashboardGuru/profil/update', [GuruController::class, 'updateGuruProfile'])->name('dashboard.guru.profile.update');
    Route::put('/dashboardGuru/profil/update/password', [GuruController::class, 'updateGuruPassword'])->name('dashboard.guru.password.update');

    Route::get('/dashboardGuru/riwayat', [GuruController::class, 'indexGururiwayat'])->name('dashboard.guru.riwayat.index');
    Route::get('/dashboardGuru/manual', [GuruController::class, 'indexGuruManual'])->name('dashboard.guru.manual.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Whatsapp retry & flush
    Route::post('/wa-retry', [DashboardController::class, 'retryAllWA'])->name('wa.retry');
    Route::post('/wa-flush', [DashboardController::class, 'flushFailedWA'])->name('wa.flush');
    // Absensi QR Code Routes
    Route::get('/scan', [AbsensiController::class, 'scanIndex'])->name('absensi.scan.index');

    // Input Absensi Manual Routes
    Route::get('/absensi-manual', [AbsensiController::class, 'manualIndex'])->name('absensi.manual.index');

    // Pengaturan Routes
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan/profil', [PengaturanController::class, 'updateProfil'])->name('pengaturan.updateProfil');
    Route::post('/pengaturan/update-system', [PengaturanController::class, 'systemUpdate'])->name('pengaturan.systemUpdate');
    Route::post('/pengaturan/backup', [PengaturanController::class, 'databaseBackup'])->name('pengaturan.backup');
    Route::get('/pengaturan/backup/download/{filename}', [PengaturanController::class, 'downloadBackup'])->name('database.backup.download');
    // Route Khusus Cetak (Bisa diakses Siswa & Guru nantinya)
    Route::get('/cetak-kartu/siswa', [CetakKartuController::class, 'cetakSiswa'])->name('cetak.kartu.siswa');
    Route::get('/cetak-kartu/guru', [CetakKartuController::class, 'cetakGuru'])->name('cetak.kartu.guru');

    Route::get('/import-excel', [ImportExcelController::class, 'showImportForm'])->name('import.excel.form');
    Route::post('/import-excel/siswa', [ImportExcelController::class, 'importSiswa'])->name('import.excel.siswa');
    Route::post('/import-excel/guru', [ImportExcelController::class, 'importGuru'])->name('import.excel.guru');

    Route::get('/rekap-harian', [RekapController::class, 'rekapHarian'])->name('rekap.harian');
    Route::get('/rekap-bulanan', [RekapController::class, 'rekapBulanan'])->name('rekap.bulanan');
    Route::get('/rekap-bulanan/export', [RekapController::class, 'exportBulananExcel'])->name('rekap.bulanan.export');

    Route::post('/siswa/hapus-banyak', [SiswaController::class, 'hapusBanyak'])->name('siswa.hapus_banyak');
    Route::post('/siswa/hapus-kelas/{kelas_id}', [SiswaController::class, 'hapusPerKelas'])->name('siswa.hapus_kelas');
    Route::post('/cetak-kartu/siswa/banyak', [CetakKartuController::class, 'cetakSiswaBanyak'])->name('cetak.kartu.siswa.banyak');

    Route::get('/cetak-kartu/siswa/kelas/{kelas_id}', [CetakKartuController::class, 'cetakSiswaPerKelas'])->name('cetak.kartu.siswa.kelas');
});

Route::prefix('kelas')->name('kelas.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [KelasController::class, 'index'])->name('index');
    Route::post('/store', [KelasController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [KelasController::class, 'edit'])->name('edit');
    Route::put('/{id}', [KelasController::class, 'update'])->name('update');
    Route::delete('/{id}', [KelasController::class, 'destroy'])->name('destroy');
});

Route::prefix('device_rfid')->name('device_rfid.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DeviceRfidController::class, 'index'])->name('index');
    Route::post('/store', [DeviceRfidController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [DeviceRfidController::class, 'edit'])->name('edit');
    Route::put('/{id}', [DeviceRfidController::class, 'update'])->name('update');
    Route::delete('/{id}', [DeviceRfidController::class, 'destroy'])->name('destroy');
});

Route::prefix('siswa')->name('siswa.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [SiswaController::class, 'index'])->name('index');
    Route::get('/create', [SiswaController::class, 'create'])->name('create');
    Route::post('/store', [SiswaController::class, 'store'])->name('store');

    Route::get('/{id}/edit', [SiswaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SiswaController::class, 'update'])->name('update');

    // Fitur Khusus
    Route::put('/{id}/keluar', [SiswaController::class, 'keluar'])->name('keluar');
    Route::post('/generate-akun', [SiswaController::class, 'generateAkun'])->name('generate_akun');
});

Route::prefix('guru')->name('guru.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [GuruController::class, 'index'])->name('index');
    Route::get('/create', [GuruController::class, 'create'])->name('create');
    Route::post('/store', [GuruController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [GuruController::class, 'edit'])->name('edit');
    Route::put('/{id}', [GuruController::class, 'update'])->name('update');

    // Fitur Khusus
    Route::put('/{id}/keluar', [GuruController::class, 'keluar'])->name('keluar');
    Route::post('/generate-akun', [GuruController::class, 'generateAkun'])->name('generate_akun');
});

Route::prefix('kartu')->name('kartu.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [KartuController::class, 'index'])->name('index');
    Route::post('/store', [KartuController::class, 'store'])->name('store');

    Route::delete('/{id}', [KartuController::class, 'destroy'])->name('destroy');
});

Route::prefix('wakel')->name('wakel.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [WakelController::class, 'index'])->name('index');
    Route::get('/create', [WakelController::class, 'create'])->name('create');
    Route::post('/store', [WakelController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [WakelController::class, 'edit'])->name('edit');
    Route::put('/{id}', [WakelController::class, 'update'])->name('update');
    Route::delete('/{id}', [WakelController::class, 'destroy'])->name('destroy');
});
