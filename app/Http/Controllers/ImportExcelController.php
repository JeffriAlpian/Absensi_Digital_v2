<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date; // Penting untuk konversi tanggal Excel

// Panggil Library Endroid
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class ImportExcelController extends Controller
{
    public function showImportForm()
    {
        return view('import.index');
    }

    public function importSiswa(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $data = Excel::toArray([], $file);

        if (empty($data) || empty($data[0])) {
            return back()->with('error', 'File Excel kosong atau format salah.');
        }

        $rows = $data[0];
        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip Header

            $rowNum = $index + 1;

            // Mapping Kolom
            $nis          = trim($row[0] ?? '');
            $nisn         = trim($row[1] ?? '');
            $nama         = trim($row[2] ?? '');
            $tempat_lahir = trim($row[3] ?? '');
            $raw_tanggal  = $row[4] ?? null;
            $nama_kelas   = trim($row[5] ?? '');
            $no_wa        = trim($row[6] ?? '');

            // 1. Validasi Dasar
            if (empty($nis) || empty($nisn) || empty($nama) || empty($nama_kelas)) {
                $gagal++;
                $errors[] = "Baris $rowNum: Data tidak lengkap (NIS, NISN, Nama, Kelas wajib diisi).";
                continue;
            }

            // 2. Konversi Tanggal
            $tanggal_lahir = null;
            if ($raw_tanggal) {
                try {
                    if (is_numeric($raw_tanggal)) {
                        $tanggal_lahir = Date::excelToDateTimeObject($raw_tanggal)->format('Y-m-d');
                    } else {
                        $tanggal_lahir = date('Y-m-d', strtotime($raw_tanggal));
                    }
                } catch (\Exception $e) {
                    $tanggal_lahir = null;
                }
            }

            // 3. Format WA
            $no_wa_valid = $this->formatNoWa($no_wa);

            // GUNAKAN TRANSACTION
            DB::beginTransaction();
            try {
                // 4. Handle User (Create User DULUAN)
                // Menggunakan NISN sebagai Username dan Password Default
                $user = User::updateOrCreate(
                    ['username' => $nisn], // Kunci pencarian (NISN unik)
                    [
                        'password' => Hash::make($nisn), // Password default = NISN
                        'role'     => 'siswa'
                    ]
                );

                // 5. Handle Kelas
                $kelas = Kelas::firstOrCreate(
                    ['nama_kelas' => strtoupper($nama_kelas)],
                    ['jam_masuk' => '07:00:00', 'jam_pulang' => '14:00:00']
                );

                // 6. Simpan / Update Siswa
                $siswa = Siswa::updateOrCreate(
                    ['nisn' => $nisn], // Kunci pencarian
                    [
                        'nis'           => $nis,
                        'nama'          => $nama,
                        'tempat_lahir'  => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'id_kelas'      => $kelas->id,
                        'no_wa'         => $no_wa_valid,
                        'user_id'       => $user->id, // <--- Foreign Key masuk disini
                        'status'        => 'aktif'
                    ]
                );

                // 7. Generate QR Code
                $this->generateQrCode($siswa->nisn);

                DB::commit(); // Simpan permanen
                $berhasil++;
            } catch (\Exception $e) {
                DB::rollBack(); // Batalkan jika error
                $gagal++;
                $errors[] = "Baris $rowNum: Gagal - " . $e->getMessage();
            }
        }

        $msgType = ($gagal > 0) ? 'warning' : 'success';
        $msg = "Import Siswa Selesai. Berhasil: $berhasil. Gagal: $gagal.";

        return back()->with([
            'status' => $msgType,
            'message' => $msg,
            'import_errors' => $errors
        ]);
    }

    // --- Placeholder untuk Guru ---
    public function importGuru(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $data = Excel::toArray([], $file);

        if (empty($data) || empty($data[0])) {
            return back()->with('error', 'File Excel kosong atau format salah.');
        }

        $rows = $data[0];
        $berhasil = 0;
        $gagal = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip Header

            $rowNum = $index + 1;

            // Mapping Kolom
            $nip          = trim($row[0] ?? '');
            $nama         = trim($row[1] ?? '');
            $jabatan      = trim($row[2] ?? '');
            $tempat_lahir = trim($row[3] ?? '');
            $raw_tanggal  = $row[4] ?? null;
            $no_wa        = trim($row[5] ?? '');
            $password     = trim($row[0] ?? '');

            // 1. Validasi Dasar
            if (empty($nip) || empty($nama) || empty($password)) {
                $gagal++;
                $errors[] = "Baris $rowNum: NIP, Nama, dan Password wajib diisi.";
                continue;
            }

            // 2. Konversi Tanggal
            $tanggal_lahir = null;
            if ($raw_tanggal) {
                try {
                    if (is_numeric($raw_tanggal)) {
                        $tanggal_lahir = Date::excelToDateTimeObject($raw_tanggal)->format('Y-m-d');
                    } else {
                        $tanggal_lahir = date('Y-m-d', strtotime($raw_tanggal));
                    }
                } catch (\Exception $e) {
                    $tanggal_lahir = null;
                }
            }

            // 3. Format WA
            $no_wa_valid = $this->formatNoWa($no_wa);

            // GUNAKAN TRANSACTION AGAR DATA AMAN
            DB::beginTransaction();
            try {
                // 4. Handle User (Update jika ada, Create jika belum)
                // Logic: Cari berdasarkan username (nip). Jika ketemu, update password & role. Jika tidak, buat baru.
                $user = User::updateOrCreate(
                    ['username' => $nip], // Kunci pencarian
                    [
                        'password' => Hash::make($password),
                        'role'     => 'guru',
                    ]
                );

                // 5. Simpan / Update Guru (Gunakan Model GURU, bukan SISWA)
                $guru = Guru::updateOrCreate(
                    ['nip' => $nip], // Kunci pencarian
                    [
                        'nama'          => $nama,
                        'jabatan'       => $jabatan,
                        'tempat_lahir'  => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'no_wa'         => $no_wa_valid,
                        'user_id'       => $user->id, // Ambil ID dari user yang sudah dihandle di atas
                        'status'        => 'aktif'
                    ]
                );

                // 6. Generate QR Code
                $this->generateQrCode($guru->nip);

                DB::commit(); // Simpan perubahan permanen
                $berhasil++;
            } catch (\Exception $e) {
                DB::rollBack(); // Batalkan semua perubahan jika ada error di baris ini
                $gagal++;
                $errors[] = "Baris $rowNum: Gagal - " . $e->getMessage();
            }
        }

        $msgType = ($gagal > 0) ? 'warning' : 'success';
        $msg = "Import Guru Selesai. Berhasil: $berhasil. Gagal: $gagal.";

        return back()->with([
            'status' => $msgType,
            'message' => $msg,
            'import_errors' => $errors
        ]);
    }

    // --- Helper Functions ---

    private function formatNoWa($no_wa)
    {
        if (empty($no_wa)) return null;
        $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
        if (substr($no_wa, 0, 1) === '0') return '62' . substr($no_wa, 1);
        if (substr($no_wa, 0, 2) === '62') return $no_wa;
        return null;
    }

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

    private function checkAndCreateUser($siswa)
    {
        // Cek jika user belum ada, buatkan
        if (!User::where('username', $siswa->nisn)->exists()) {
            User::create([
                'username' => $siswa->nisn,
                'password' => Hash::make($siswa->nisn),
                'role' => 'siswa'
            ]);
        }
    }
}
