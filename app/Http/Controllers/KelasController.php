<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        $data_kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        // Pastikan variabel $kelas_edit dikirim sebagai null agar tidak error di view
        return view('kelas.index', [
            'data_kelas' => $data_kelas,
            'kelas_edit' => null 
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|unique:kelas,nama_kelas',
            'jam_masuk'  => 'required',
            'jam_pulang' => 'required',
        ]);

        Kelas::create($request->all());

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambah.');
    }

    // LOGIKA BARU: EDIT MENGARAH KEMBALI KE INDEX
    public function edit($id)
    {
        // 1. Ambil data yang mau diedit
        $kelas_edit = Kelas::findOrFail($id);

        // 2. Ambil semua data untuk tabel (sama seperti index)
        $data_kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        // 3. Return view 'index' tapi bawa variabel $kelas_edit
        return view('kelas.index', compact('data_kelas', 'kelas_edit'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|unique:kelas,nama_kelas,'.$id,
            'jam_masuk'  => 'required',
            'jam_pulang' => 'required',
        ],
        [
            'nama_kelas.required' => 'Nama Kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama Kelas sudah terdaftar.',
            'jam_masuk.required' => 'Jam Masuk wajib diisi.',
            'jam_pulang.required' => 'Jam Pulang wajib diisi.',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());

        return redirect()->route('kelas.index')->with('success', 'Data Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Kelas::findOrFail($id)->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}