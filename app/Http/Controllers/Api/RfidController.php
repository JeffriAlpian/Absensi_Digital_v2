<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class RfidController extends Controller
{
    public function getSiswaList(Request $request)
    {
        // 1. Validasi API Key
        // Sebaiknya simpan secret ini di .env, tapi hardcode dulu oke untuk sekarang
        $secret = env('RFID_API_SECRET');

        // Ambil parameter ?key=... dari URL
        if ($request->query('key') !== $secret) {
            return response()->json(['success' => 'error', 'message' => 'Invalid API Key']);
        }

        // 2. Query Database (Pengganti Native SQL kamu)
        // Kita cari siswa aktif yang TIDAK ada di tabel kartu_rfid
        $siswa = Siswa::leftJoin('kartu_rfid', 'siswa.id', '=', 'kartu_rfid.siswa_id')
            ->where('siswa.status', 'aktif')
            ->whereNull('kartu_rfid.id') // Filter yang belum punya kartu (Join-nya NULL)
            ->orderBy('siswa.nama', 'asc')
            ->select('siswa.id', 'siswa.nama', 'siswa.nisn') // Ambil kolom yang dibutuhkan saja
            ->get();

        // 3. Return JSON
        // Laravel otomatis mengubah Collection jadi JSON
        return response()->json($siswa);
    }

    public function getKelasList(Request $request)
    {
        // 1. Validasi API Key
        $secret = env('RFID_API_SECRET');
        if ($request->query('key') !== $secret) {
            return response()->json(['success' => 'error', 'message' => 'Invalid API Key']);
        }
        // 2. Query Database untuk ambil daftar kelas
        $kelas = DB::table('kelas')
            ->orderBy('nama_kelas', 'asc')
            ->select('id', 'nama_kelas')
            ->get();
        // 3. Return JSON
        return response()->json($kelas);
    }

    public function registerRfid(Request $request)
    {
        // 1. Validasi API Key
        $secret = env('RFID_API_SECRET');
        if ($request->query('key') !== $secret) {
            return response()->json(['success' => 'error', 'message' => 'Invalid API Key']);
        }

        // 2. Validasi Input
        $siswa_id = intval($request->input('siswa_id'));
        $device_id = intval($request->input('device_id'));
        $uid = trim($request->input('uid'));
        if (empty($siswa_id) || empty($uid)) {
            return response()->json(['success' => 'error', 'message' => 'Data tidak lengkap']);
        }
        // 3. Cek Duplikat
        $exists = DB::table('kartu_rfid')->where('uid', $uid)->exists();
        if ($exists) {
            return response()->json(['success' => 'error', 'message' => 'Kartu ini sudah terdaftar!']);
        }
        // 4. Simpan ke Database
        $inserted = DB::table('kartu_rfid')->insert([
            'uid' => $uid,
            'siswa_id' => $siswa_id,
            'device_id' => $device_id,
            'registed_at' => now()
        ]);
        if ($inserted) {
            return response()->json(['success' => 'success', 'message' => 'Kartu berhasil didaftarkan']);
        } else {
            return response()->json(['success' => 'error', 'message' => 'Gagal menyimpan data']);
        }

    }
    
}
