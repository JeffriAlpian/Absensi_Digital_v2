<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\RfidModel;

class DeviceRfidController extends Controller
{
    public function index()
    {
        $data_device_rfid = RfidModel::all();
        return view('device_rfid.index', [
            'data_device_rfid' => $data_device_rfid,
            'device_rfid_edit' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rfid_name' => 'required|unique:rfid_model,rfid_name',
        ]);

        // 1. Ambil semua data request
        $data = $request->all();

        // 2. Tambahkan API Key otomatis ke dalam array data
        // Str::random(32) akan membuat 32 karakter acak
        $data['api_key'] = Str::random(32);

        // 3. Create data menggunakan array yang sudah dimodifikasi
        RfidModel::create($data);

        return redirect()->route('device_rfid.index')->with('success', 'Device RFID berhasil ditambah.');
    }

    public function edit($id)
    {
        // 1. Ambil data yang mau diedit
        $device_rfid_edit = RfidModel::findOrFail($id);

        // 2. Ambil semua data untuk tabel (agar tabel tetap muncul)
        $data_device_rfid = RfidModel::orderBy('created_at', 'desc')->get();

        // 3. Return ke view index, tapi bawa variabel edit
        return view('device_rfid.index', [
            'data_device_rfid' => $data_device_rfid,
            'device_rfid_edit' => $device_rfid_edit
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            // Unique ignore ID saat ini
            'rfid_name' => 'required|unique:rfid_model,rfid_name,'.$id,
        ]);

        $device = RfidModel::findOrFail($id);
        
        // Kita hanya update nama device, API KEY biarkan tetap (agar tidak berubah-ubah)
        $device->update([
            'rfid_name' => $request->rfid_name
        ]);

        return redirect()->route('device_rfid.index')->with('success', 'Nama Device berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $device = RfidModel::findOrFail($id);
        $device->delete();

        return redirect()->route('device_rfid.index')->with('success', 'Device berhasil dihapus.');
    }
}
