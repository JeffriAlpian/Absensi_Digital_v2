<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Library QR

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Fitur Pencarian (Pengganti siswa_cari.php)
        $query = Siswa::with('kelas')->where('status', 'aktif');

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $data_siswa = $query->latest()->get();

        return view('siswa.index', compact('data_siswa'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        return view('siswa.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nis' => 'required|unique:siswa,nis',
            'nisn' => 'required|unique:siswa,nisn',
            'nama' => 'required',
            'id_kelas' => 'required',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'id_kelas.required' => 'Kelas wajib dipilih.',
        ]);

        DB::transaction(function () use ($request) {

            User::create([
                'username' => $request->nisn,
                'password' => Hash::make($request->nisn),
                'role' => 'siswa'
            ]);

            // 2. Simpan Siswa
            $siswa = Siswa::create([
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama' => $request->nama,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'id_kelas' => $request->id_kelas,
                'no_wa' => $request->no_wa,
                'status' => 'aktif',
            ]);

            // 3. Generate QR Code
            $this->generateQrCode($siswa->nisn);

        });

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil disimpan!');
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::all();
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $old_nisn = $siswa->nisn;

        // Validasi
        $request->validate([
            'nis' => 'required|unique:siswa,nis,' . $id,
            'nisn' => 'required|unique:siswa,nisn,' . $id,
            'nama' => 'required',
        ]);

        // Update Data
        $siswa->update($request->all());

        // Cek jika NISN berubah, update User & QR
        if ($old_nisn !== $request->nisn) {
            // Update Username di tabel User
            User::where('username', $old_nisn)->update([
                'username' => $request->nisn,
                'password' => Hash::make($request->nisn) // Reset password ke NISN baru
            ]);

            // Hapus QR lama & Buat baru
            if (Storage::exists('public/qr/' . $old_nisn . '.png')) {
                Storage::delete('public/qr/' . $old_nisn . '.png');
            }
            $this->generateQrCode($request->nisn);
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa diperbarui!');
    }

    public function keluar($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->update(['status' => 'nonaktif']); // Atau 'alumni'

        // Opsional: Matikan user loginnya
        User::where('username', $siswa->nisn)->delete();

        return redirect()->back()->with('success', 'Siswa ditandai keluar/lulus.');
    }

    public function generateAkun()
    {
        $siswa_aktif = Siswa::where('status', 'aktif')->get();
        $count = 0;

        foreach ($siswa_aktif as $s) {
            // Cek apakah user sudah ada
            $user = User::where('username', $s->nisn)->first();

            if (!$user) {
                User::create([
                    'username' => $s->nisn,
                    'password' => Hash::make($s->nisn), // Default password = NISN
                    'role' => 'siswa'
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil generate $count akun siswa.");
    }

    // --- Helper Functions ---

    private function generateQrCode($code)
    {
        $imageContent = QrCode::format('png')->size(300)->generate($code);
        Storage::disk('public')->put('qr/' . $code . '.png', $imageContent);
    }

}
