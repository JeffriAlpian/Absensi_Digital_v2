<?php

namespace App\Http\Controllers;

use App\Models\Guru;
// use App\Models\WaliKelas;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


// Panggil Library Endroid
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        // Fitur Pencarian (Pengganti siswa_cari.php)
        $query = Guru::where('status', 'aktif');

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $data_guru = $query->latest()->get();

        return view('guru.index', compact('data_guru'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nip' => 'required|unique:guru,nip',
            'nama' => 'required',
            'jabatan' => 'required',
            'password' => 'required|min:6',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // GUNAKAN TRANSACTION AGAR LEBIH AMAN
        DB::transaction(function () use ($request) {

            // 2. BUAT USER TERLEBIH DAHULU
            // Kita butuh ID user ini untuk dimasukkan ke tabel guru
            $user = User::create([
                'username' => $request->nip,
                'password' => Hash::make($request->password),
                'role' => 'guru',
            ]);

            // 3. BUAT GURU (Sertakan user_id)
            $guru = Guru::create([
                'user_id' => $user->id, // <--- INI KUNCINYA
                'nip' => $request->nip,
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'status' => 'aktif',
                'no_wa' => $request->no_wa,
            ]);

            // 4. Generate QR Code
            $this->generateQrCode($guru->nip);
        });

        return redirect()->route('guru.index')->with('success', 'Guru dan Akun Login berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $guru = Guru::findOrFail($id);
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        $old_nip = $guru->nip;
        // Validasi Input
        $request->validate([
            'nip' => 'required|unique:guru,nip,' . $guru->id,
            'nama' => 'required',
            'jabatan' => 'required',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        // Update Data Guru
        $guru->update($request->all());

        if ($old_nip != $request->nip) {
            // Update juga data usernya
            User::where('username', $old_nip)->update([
                'username' => $request->nip,
                'password' => Hash::make($request->password)
            ]);

            // Hapus QR Code lama
            if (Storage::exists('public/qr/' . $old_nip . '.png')) {
                Storage::delete('public/qr/' . $old_nip . '.png');
            }
            // Generate QR Code baru
            $this->generateQrCode($request->nip);
        }

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function generateAkun()
    {
        // Ambil guru aktif yang memiliki NIP (jaga-jaga jika ada data kosong)
        $guru_aktif = Guru::where('status', 'aktif')
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->get();

        $count = 0;
        $updated = 0;

        foreach ($guru_aktif as $g) {
            // 1. Cari atau Buat User
            $user = User::firstOrCreate(
                ['username' => $g->nip],
                [
                    'password' => Hash::make($g->nip),
                    'role'     => 'guru',
                    // 'name'  => $g->nama // Opsional: jika ingin nama di user ikut nama guru
                ]
            );

            // Hitung jika user baru
            if ($user->wasRecentlyCreated) {
                $count++;
            }

            // --- PERBAIKAN DI SINI ---
            // 2. Cek apakah Role-nya sudah benar 'guru'? Jika belum, paksa ubah.
            // Ini menangani kasus user sudah ada (first) tapi role-nya masih default ('siswa')
            if ($user->role !== 'guru') {
                $user->role = 'guru';
                $user->save(); // Simpan perubahan role
            }
            // -------------------------

            // 3. UPDATE user_id di tabel guru
            if ($g->user_id != $user->id) {
                $g->user_id = $user->id;
                $g->save();
                $updated++;
            }
        }

        return redirect()->back()->with('success', "Selesai! $count akun baru dibuat, $updated data guru ditautkan.");
    }

    // Helper function
    private function generateQrCode($code)
    {
        // 1. Generate QR Code Object
        $result = Builder::create()
            ->writer(new PngWriter()) // Menggunakan GD (Aman untuk shared hosting)
            ->writerOptions([])
            ->data($code)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(300)
            ->margin(10)
            ->build();

        // 2. Simpan ke Storage (Folder: storage/app/public/qr)
        // getString() mengubah gambar menjadi string binary agar bisa disimpan oleh Storage::put
        Storage::disk('public')->put('qr/' . $code . '.png', $result->getString());
    }


    // =======================================================================
    // Dashboard Guru Function
    // =======================================================================
    public function indexGuruScan()
    {
        return view('dashboard.guru.scan');
    }


    public function indexGuruAbsen()
    {
        $user = Auth::user();
        $data_guru = Guru::where('user_id', $user->id)->first();
        return view('dashboard.guru.absen', compact('data_guru'));
    }


    public function indexGuruProfile()
    {
        $user = Auth::user();
        $data_guru = Guru::where('user_id', $user->id)->first();
        return view('dashboard.guru.profil', compact('data_guru', 'user'));
    }


    public function updateGuruProfile(Request $request)
    {
        $user = Auth::user();
        /** @var User $user */
        $guru = Guru::where('user_id', $user->id)->first();
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required',
        ]);

        if ($user->update(['username' => $request->username,]) || $guru->update(['nama' => $request->name])) {
            return back()->with('success', 'Biodata berhasil diperbarui.');
        } else {
            return back()->with('error', 'Biodata gagal diperbarui.');
        }
    }


    public function updateGuruPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed', // field konfirmasi harus bernama 'password_confirmation'
        ]);

        $user = Auth::user();
        /** @var User $user */

        // Cek apakah password lama benar
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah!']);
        }

        // Update Password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diganti!');
    }


    public function indexGuruSettings()
    {
        return view('dashboard.guru.setting');
    }


    public function indexGuruRiwayat(Request $request)
    {
        $user = Auth::user(); // Asumsi login sebagai user guru

        $guru = Guru::where('user_id', $user->id)->first();

        // Validasi: Jika data guru belum ditautkan
        if (!$guru) {
            return redirect()->back()->with('error', 'Data profil guru tidak ditemukan. Hubungi Admin.');
        }

        // Default tanggal hari ini dan awal bulan
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-d');

        // Query Data Absensi
        $query = Absensi::where('guru_id', $guru->id) // Sesuaikan nama tabel/relasi
            ->whereBetween('tanggal', [$startDate, $endDate]);

        // Data untuk Tabel
        $riwayat = $query->orderBy('tanggal', 'desc')->paginate(10);

        // Data untuk Summary (Ringkasan)
        // clone() digunakan agar query utama tidak berubah saat dihitung
        $summary = [
            'hadir' => (clone $query)->where('status', 'H')->count(),
            'telat' => (clone $query)->where('keterangan', 'Terlambat')->count(),
            'izin'  => (clone $query)->whereIn('status', ['I', 'S'])->count(),
            'alpha' => (clone $query)->where('status', 'A')->count(),
        ];

        return view('dashboard.guru.riwayat', compact('riwayat', 'summary'));
    }
}
