<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLibur = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('hari-libur.index', compact('hariLibur'));
    }

    public function create()
    {
        return view('hari-libur.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'nullable|string|max:100',
        ]);

        HariLibur::create($request->only('tanggal', 'keterangan'));

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        return view('hari-libur.edit', compact('hariLibur'));
    }

    public function update(Request $request, $id)
    {
        $hariLibur = HariLibur::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal,' . $hariLibur->id,
            'keterangan' => 'nullable|string|max:100',
        ]);

        $hariLibur->update($request->only('tanggal', 'keterangan'));

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->delete();

        return redirect()->route('hari-libur.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }
}