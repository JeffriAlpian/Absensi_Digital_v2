<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\KartuRfid;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with('kartu_rfid')
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%");
            });
        }

        $guru = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $guru
        ]);
    }

    public function getBelumRfid(Request $request)
    {
        // Ambil semua guru yang aktif
        $allGuru = Guru::where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil guru yang sudah memiliki RFID
        $guruWithRfid = KartuRfid::whereNotNull('guru_id')
            ->pluck('guru_id')
            ->toArray();

        // Filter guru yang belum memiliki RFID
        $guruBelumRfid = $allGuru->filter(function ($guru) use ($guruWithRfid) {
            return !in_array($guru->id, $guruWithRfid);
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Data guru yang belum memiliki kartu RFID',
            'data' => $guruBelumRfid,
            'total' => $guruBelumRfid->count()
        ]);
    }
}