<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\RfidModel;

class KartuController extends Controller
{
    public function index()
    {
        // 1. Ambil data untuk Dropdown (Hanya user yang BELUM punya kartu)
        // 'whereDoesntHave' artinya: Cari siswa yang tidak punya relasi di tabel kartu_rfid
        $siswa_belum_ada_kartu = Siswa::whereDoesntHave('kartuRfid')->orderBy('nama', 'asc')->get();
        $guru_belum_ada_kartu  = Guru::whereDoesntHave('kartuRfid')->orderBy('nama', 'asc')->get();

        // 2. Ambil data untuk Tabel (Kartu yang SUDAH terdaftar)
        // Kita pisah query agar tabel di Blade lebih rapi
        $kartu_siswa = KartuRfid::whereNotNull('siswa_id')
            ->with('siswa') // Load relasi siswa agar nama muncul
            ->latest()
            ->get();

        $kartu_guru  = KartuRfid::whereNotNull('guru_id')
            ->with('guru') // Load relasi guru
            ->latest()
            ->get();

        $device_models = RfidModel::all();

        // Kirim semua variabel ke view
        return view('kartu.index', compact(
            'siswa_belum_ada_kartu',
            'guru_belum_ada_kartu',
            'kartu_siswa',
            'kartu_guru',
            'device_models'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'uid'     => 'required|unique:kartu_rfid,uid', // UID tidak boleh kembar
            'user_id' => 'required', // ID Siswa atau ID Guru
            'device_id' => 'required',
            'tipe'    => 'required|in:siswa,guru', // Memastikan tipe hanya bisa 'siswa' atau 'guru'
        ], [
            'uid.unique' => 'Kartu RFID ini sudah terdaftar sebelumnya!',
            'uid.required' => 'UID Kartu wajib diisi (Scan kartu).',
            'device_id.required' => 'Silakan pilih device.',
            'user_id.required' => 'Silakan pilih nama pemilik kartu.',
        ]);

        // 2. Siapkan data dasar
        $data = [
            'uid'    => $request->uid,
            'device_id' => $request->device_id,
        ];

        // 3. Cek Tipe: Masukkan ke kolom siswa_id ATAU guru_id
        if ($request->tipe == 'siswa') {
            $data['siswa_id'] = $request->user_id;
            $data['guru_id']  = null;
        } else {
            $data['guru_id']  = $request->user_id;
            $data['siswa_id'] = null;
        }

        // 4. Simpan ke Database
        KartuRfid::create($data);

        // 5. Kembali ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('kartu.index')->with('success', 'Kartu RFID berhasil didaftarkan!');
    }

    public function destroy($id)
    {
        // Cari data berdasarkan ID, jika tidak ada tampilkan error 404
        $kartu = KartuRfid::findOrFail($id);

        // Hapus
        $kartu->delete();

        return redirect()->route('kartu.index')->with('success', 'Data Kartu RFID berhasil dihapus.');
    }
}
