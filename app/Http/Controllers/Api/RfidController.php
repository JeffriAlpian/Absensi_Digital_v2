<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RfidController extends Controller
{
    public function testConnection()
    {
        return response()->json([
            'success' => true,
            'message' => 'API Connected Successfully',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function registerRfid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required|string|max:50|unique:kartu_rfid,uid',
            'siswa_id' => 'nullable|exists:siswa,id',
            'guru_id' => 'nullable|exists:guru,id',
            'device_id' => 'required|exists:rfid_model,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah UID sudah terdaftar
        if (KartuRfid::where('uid', $request->uid)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'UID sudah terdaftar'
            ], 400);
        }

        // Validasi: siswa_id dan guru_id tidak boleh keduanya diisi
        if ($request->has('siswa_id') && $request->has('guru_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya boleh memilih siswa ATAU guru, tidak keduanya'
            ], 400);
        }

        // Cek apakah siswa sudah memiliki kartu RFID
        if ($request->has('siswa_id') && $request->siswa_id) {
            $existing = KartuRfid::where('siswa_id', $request->siswa_id)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa ini sudah memiliki kartu RFID'
                ], 400);
            }
        }

        // Cek apakah guru sudah memiliki kartu RFID
        if ($request->has('guru_id') && $request->guru_id) {
            $existing = KartuRfid::where('guru_id', $request->guru_id)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ini sudah memiliki kartu RFID'
                ], 400);
            }
        }

        try {
            $kartuRfid = KartuRfid::create([
                'uid' => strtoupper($request->uid),
                'siswa_id' => $request->siswa_id,
                'guru_id' => $request->guru_id,
                'device_id' => $request->device_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kartu RFID berhasil didaftarkan',
                'data' => [
                    'id' => $kartuRfid->id,
                    'uid' => $kartuRfid->uid,
                    'siswa_id' => $kartuRfid->siswa_id,
                    'guru_id' => $kartuRfid->guru_id,
                    'device_id' => $kartuRfid->device_id,
                    'registered_at' => $kartuRfid->registed_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = KartuRfid::with(['siswa', 'guru', 'device'])
            ->orderBy('created_at', 'desc');

        if ($request->has('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->has('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        if ($request->has('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        $kartuRfid = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $kartuRfid
        ]);
    }

    public function destroy($id)
    {
        try {
            $kartuRfid = KartuRfid::findOrFail($id);
            $kartuRfid->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kartu RFID berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kartu: ' . $e->getMessage()
            ], 500);
        }
    }
}