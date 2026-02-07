<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\KartuRfid;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['kartu_rfid'])
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nisn', 'like', "%$search%")
                  ->orWhere('nis', 'like', "%$search%");
            });
        }

        $siswa = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }

    public function getBelumRfid(Request $request)
    {
        // Ambil semua siswa yang aktif
        $query = Siswa::where('siswa.status', 'aktif')
            ->orderBy('siswa.nama', 'asc');

        // Ambil semua siswa
        $allSiswa = $query->get();

        // Ambil siswa yang sudah memiliki RFID
        $siswaWithRfid = KartuRfid::whereNotNull('siswa_id')
            ->pluck('siswa_id')
            ->toArray();

        // Filter siswa yang belum memiliki RFID
        $siswaBelumRfid = $allSiswa->filter(function ($siswa) use ($siswaWithRfid) {
            return !in_array($siswa->id, $siswaWithRfid);
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Data siswa yang belum memiliki kartu RFID',
            'data' => $siswaBelumRfid,
            'total' => $siswaBelumRfid->count()
        ]);
    }
}