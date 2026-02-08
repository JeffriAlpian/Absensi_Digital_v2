<?php

use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\AbsensiRfidController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\DeviceController;

// Test connection
Route::get('/test-connection', [RfidController::class, 'testConnection']);

// Device routes
Route::get('/devices', [DeviceController::class, 'index']);
Route::post('/devices', [DeviceController::class, 'store']);
Route::put('/devices/{id}', [DeviceController::class, 'update']);
Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);

// Siswa routes
Route::get('/siswa/belum-rfid', [SiswaController::class, 'getBelumRfid']);
Route::get('/siswa', [SiswaController::class, 'index']);

// Guru routes
Route::get('/guru/belum-rfid', [GuruController::class, 'getBelumRfid']);
Route::get('/guru', [GuruController::class, 'index']);

// RFID Registration
Route::post('/register-rfid', [RfidController::class, 'registerRfid']);

// RFID Management
Route::get('/kartu-rfid', [RfidController::class, 'index']);
Route::delete('/kartu-rfid/{id}', [RfidController::class, 'destroy']);


// URL untuk Alat: http://localhost:8000/api/rfid/catat dengan method POST dengan body raw JSON { "uid": "xxxxxx", "api_key": "xxxxxxxxxx" }
Route::post('/rfid/catat', [AbsensiRfidController::class, 'catatAbsensi']);
