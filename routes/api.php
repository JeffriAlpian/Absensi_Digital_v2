<?php

use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\AbsensiRfidController;
use Illuminate\Support\Facades\Route;

// Contoh URL: http://localhost:8000/api/get-siswa-list?key=xxxxx
Route::get('/get-siswa-list', [RfidController::class, 'getSiswaList']);
Route::get('/get-kelas-list', [RfidController::class, 'getKelasList']);
Route::post('/register-rfid', [RfidController::class, 'registerRfid']);

// URL untuk Alat: http://localhost:8000/api/rfid/catat dengan method POST dengan body raw JSON { "uid": "xxxxxx", "api_key": "xxxxxxxxxx" }
Route::post('/rfid/catat', [AbsensiRfidController::class, 'catatAbsensi']);