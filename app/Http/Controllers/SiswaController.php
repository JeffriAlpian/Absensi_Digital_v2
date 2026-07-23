<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
// Panggil Library Endroid
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Format;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data kelas untuk dropdown filter
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $query = Siswa::with('kelas')->where('status', 'aktif');

        // Filter Pencarian Text
        if ($request->has('q') && $request->q != '') {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Filter Berdasarkan Kelas
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $query->where('id_kelas', $request->kelas_id);
        }

        $data_siswa = $query->latest()->get();

        // Kirim $kelas ke view juga
        return view('siswa.index', compact('data_siswa', 'kelas'));
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
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:50000',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'id_kelas.required' => 'Kelas wajib dipilih.',
            'foto.image' => 'File harus berupa gambar.',
        ]);

        DB::transaction(function () use ($request) {

            User::create([
                'username' => $request->nisn,
                'password' => Hash::make($request->nisn),
                'role' => 'siswa',
            ]);

            // Proses Upload & Kompresi Foto
            // Proses Upload & Kompresi Foto
            $nama_foto = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $nama_foto = $request->nisn.'-'.time().'.jpg';

                // Inisialisasi Manager dengan Driver GD
                $manager = ImageManager::usingDriver(GdDriver::class);

                // read image data from path
                $image = $manager->decodePath($foto->path());

                $image->scale(height: 500);

                // encode edited image
                $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 75);

                // Simpan ke storage
                Storage::disk('public')->put('foto_siswa/' . $nama_foto, (string) $encoded);
            }

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
                'foto' => $nama_foto,
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
            'nis' => 'required|unique:siswa,nis,'.$id,
            'nisn' => 'required|unique:siswa,nisn,'.$id,
            'nama' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:50000',
        ]);

        $data_update = $request->except(['foto']);

       // Proses Update Foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($siswa->foto && Storage::exists('public/foto_siswa/'.$siswa->foto)) {
                Storage::delete('public/foto_siswa/'.$siswa->foto);
            }

            $foto = $request->file('foto');
            $nama_foto = $request->nisn . '-' . time() . '.jpg';
            
            // Kompresi dengan sintaks baru
            $manager = ImageManager::usingDriver(GdDriver::class);
            $image = $manager->decodePath($foto->path());
            $image->scale(width: 500);
            $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 75);
            
            Storage::disk('public')->put('foto_siswa/' . $nama_foto, (string) $encoded);
            
            $data_update['foto'] = $nama_foto;
        }

        // Update Data
        $siswa->update($data_update);

        // Cek jika NISN berubah, update User & QR
        if ($old_nisn !== $request->nisn) {
            // Update Username di tabel User
            User::where('username', $old_nisn)->update([
                'username' => $request->nisn,
                'password' => Hash::make($request->nisn), // Reset password ke NISN baru
            ]);

            // Hapus QR lama & Buat baru
            if (Storage::exists('public/qr/'.$old_nisn.'.png')) {
                Storage::delete('public/qr/'.$old_nisn.'.png');
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
        // Ambil siswa aktif yang memiliki NIP (jaga-jaga jika ada data kosong)
        $siswa_aktif = Siswa::where('status', 'aktif')
            ->whereNotNull('nisn')
            ->where('nisn', '!=', '')
            ->get();

        $count = 0;
        $updated = 0;

        foreach ($siswa_aktif as $s) {
            // 1. Cari User berdasarkan username (NIP), atau Buat baru jika tidak ada
            $user = User::firstOrCreate(
                ['username' => $s->nisn], // Kondisi pencarian
                [
                    'password' => Hash::make($s->nisn), // Data jika dibuat baru
                    'role' => 'siswa',
                ]
            );

            // Hitung jika user baru saja dibuat
            if ($user->wasRecentlyCreated) {
                $count++;
            }

            // 2. UPDATE user_id di tabel siswa
            // Cek dulu apakah user_id-nya beda/kosong supaya tidak query update jika sudah sesuai
            if ($s->user_id != $user->id) {
                $s->user_id = $user->id;
                $s->save(); // Simpan perubahan ke tabel siswa
                $updated++;
            }
        }

        return redirect()->back()->with('success', "Selesai! $count akun baru dibuat, $updated data siswa ditautkan.");
    }

    // --- TAMBAHAN BULK ACTION & KELAS ---

    public function hapusBanyak(Request $request)
    {
        $ids = $request->input('ids'); // Mengambil array ID dari checkbox

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu siswa untuk dihapus.');
        }

        // Cari siswa yang di-ceklist
        $siswas = Siswa::whereIn('id', $ids)->get();

        foreach ($siswas as $siswa) {
            $siswa->update(['status' => 'nonaktif']);
            User::where('username', $siswa->nisn)->delete();
        }

        return redirect()->back()->with('success', count($ids).' siswa terpilih berhasil dikeluarkan/dihapus.');
    }

    public function hapusPerKelas($kelas_id)
    {
        // Cari semua siswa aktif di kelas tersebut
        $siswas = Siswa::where('id_kelas', $kelas_id)->where('status', 'aktif')->get();
        $count = $siswas->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
        }

        foreach ($siswas as $siswa) {
            $siswa->update(['status' => 'nonaktif']);
            User::where('username', $siswa->nisn)->delete();
        }

        return redirect()->back()->with('success', $count.' siswa di kelas tersebut berhasil dikeluarkan/dihapus.');
    }

    // --- Helper Functions ---
    private function generateQrCode($code)
    {
        // 1. Generate QR Code Object
        $result = Builder::create()
            ->writer(new PngWriter) // Menggunakan GD (Aman untuk shared hosting)
            ->writerOptions([])
            ->data($code)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(300)
            ->margin(10)
            ->build();

        // 2. Simpan ke Storage (Folder: storage/app/public/qr)
        // getString() mengubah gambar menjadi string binary agar bisa disimpan oleh Storage::put
        Storage::disk('public')->put('qr/'.$code.'.png', $result->getString());
    }
}
