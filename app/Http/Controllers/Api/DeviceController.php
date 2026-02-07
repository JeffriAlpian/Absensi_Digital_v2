<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfidModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = RfidModel::orderBy('rfid_name', 'asc')
        ->select([
            'id',
            'rfid_name as nama_device',
        ])
        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rfid_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $device = RfidModel::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil ditambahkan',
            'data' => $device
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $device = RfidModel::find($id);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rfid_name' => 'sometimes|required|string|max:100',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $device->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diperbarui',
            'data' => $device
        ]);
    }

    public function destroy($id)
    {
        $device = RfidModel::find($id);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ], 404);
        }

        // Cek apakah device memiliki kartu RFID
        if ($device->kartuRfid()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus device yang masih memiliki kartu RFID terdaftar'
            ], 400);
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil dihapus'
        ]);
    }
}