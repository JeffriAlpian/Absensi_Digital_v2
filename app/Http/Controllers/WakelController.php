<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaliKelas;
use App\Models\Kelas;
use App\Models\Guru;

class WakelController extends Controller
{
    public function index()
    {
        // Ambil semua data dari tabel wali_kelas beserta relasinya
        $wakelList = WaliKelas::with(['kelas', 'guru'])
            ->get()
            ->sortBy(function($query) {
                return $query->kelas->nama_kelas ?? ''; // Urutkan berdasarkan nama kelas
            });

        return view('wakel.index', compact('wakelList'));
    }

    public function create()
    {
        // Cari ID kelas yang SUDAH ada di tabel wali_kelas
        $kelasTerpakai = WaliKelas::pluck('kelas_id')->toArray();

        // Ambil kelas yang BELUM memiliki wali kelas (belum ada di tabel wali_kelas)
        $kelasList = Kelas::whereNotIn('id', $kelasTerpakai)->orderBy('nama_kelas', 'asc')->get();
        
        // Ambil semua data guru
        $guruList = Guru::orderBy('nama', 'asc')->get();

        return view('wakel.create', compact('kelasList', 'guruList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id|unique:wali_kelas,kelas_id', // Pastikan 1 kelas 1 wakel
            'guru_id'  => 'required|exists:guru,id',
        ], [
            'kelas_id.required' => 'Silakan pilih kelas terlebih dahulu.',
            'kelas_id.unique'   => 'Kelas ini sudah memiliki Wali Kelas.',
            'guru_id.required'  => 'Silakan pilih guru pengampu.'
        ]);

        // Simpan ke tabel wali_kelas
        WaliKelas::create([
            'kelas_id' => $request->kelas_id,
            'guru_id'  => $request->guru_id,
        ]);

        return redirect()->route('wakel.index')->with('success', 'Wali Kelas berhasil ditugaskan.');
    }

    public function edit($id)
    {
        // Cari data wali_kelas berdasarkan ID-nya
        $wakel = WaliKelas::findOrFail($id);
        $guruList = Guru::orderBy('nama', 'asc')->get();

        return view('wakel.edit', compact('wakel', 'guruList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
        ]);

        // Update guru_id pada data wali_kelas
        $wakel = WaliKelas::findOrFail($id);
        $wakel->update([
            'guru_id' => $request->guru_id
        ]);

        return redirect()->route('wakel.index')->with('success', 'Wali Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Hapus record dari tabel wali_kelas
        $wakel = WaliKelas::findOrFail($id);
        $wakel->delete();

        return redirect()->route('wakel.index')->with('success', 'Wali Kelas berhasil dicopot.');
    }
}