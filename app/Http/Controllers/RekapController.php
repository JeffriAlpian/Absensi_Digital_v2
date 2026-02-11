<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\ProfilSekolah;
use App\Models\HariLibur;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;

class RekapController extends Controller
{
    public function rekapHarian(Request $request)
    {
        // 1. Filter Tanggal & Kelas
        $tanggal = $request->date ?? Carbon::now()->format('Y-m-d');
        $kelasId = $request->kelas_id; // Filter khusus siswa

        $formattedDate = Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        $listKelas = Kelas::orderBy('nama_kelas')->get();

        // ==========================================
        // DATA GURU
        // ==========================================
        $rekapGuru = Guru::with(['absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();

        // Statistik Guru
        $statsGuru = [
            'total'     => $rekapGuru->count(),
            'hadir'     => $rekapGuru->filter(fn($g) => $g->absensi->first() && $g->absensi->first()->status == 'H')->count(),
            'terlambat' => $rekapGuru->filter(fn($g) => $g->absensi->first() && str_contains($g->absensi->first()->keterangan, 'Terlambat'))->count(),
            'alpha'     => $rekapGuru->filter(fn($g) => !$g->absensi->first())->count(),
        ];

        // ==========================================
        // DATA SISWA
        // ==========================================
        $querySiswa = Siswa::with(['kelas', 'absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
            ->where('status', 'aktif')
            ->orderBy('nama', 'asc');

        // Jika ada filter kelas, pasang where
        if ($kelasId) {
            $querySiswa->where('id_kelas', $kelasId);
        }

        $rekapSiswa = $querySiswa->get();

        // Statistik Siswa (Berdasarkan data yang ditarik/difilter)
        $statsSiswa = [
            'total' => $rekapSiswa->count(),
            'hadir' => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'H')->count(),
            'sakit' => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'S')->count(),
            'izin'  => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'I')->count(),
            'alpha' => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'A')->count(),
            'belum' => $rekapSiswa->filter(fn($s) => !$s->absensi->first())->count(), // Belum ada data sama sekali
        ];

        return view('absensi.rekap.harian', compact(
            'rekapGuru',
            'statsGuru',
            'rekapSiswa',
            'statsSiswa',
            'tanggal',
            'formattedDate',
            'listKelas',
            'kelasId'
        ));
    }

    // =========================================================================
    // 1. TAMPILAN WEB
    // =========================================================================
    public function rekapBulanan(Request $request)
    {
        $data = $this->getDataBulanan($request);
        return view('absensi.rekap.bulanan', $data);
    }

    // =========================================================================
    // 2. EXPORT EXCEL
    // =========================================================================
    public function exportBulananExcel(Request $request)
    {
        $profil = ProfilSekolah::first();
        $data = $this->getDataBulanan($request); // Pastikan ini me-return data yang valid

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- A. SETUP HEADER ---
        $bulanStr = Carbon::createFromDate(null, $data['bulan'])->translatedFormat('F');
        $sheet->setCellValue('A1', 'REKAP ABSENSI ' . strtoupper($data['kategori']));
        $sheet->setCellValue('A2', "Bulan: $bulanStr {$data['tahun']}");

        // Styling Judul
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

        // Header Table (NO & NAMA) - Merge ke bawah sampai baris 6
        $sheet->setCellValue('A4', 'NO');
        $sheet->mergeCells('A4:A6');
        $sheet->setCellValue('B4', 'NAMA');
        $sheet->mergeCells('B4:B6');

        // --- LOOP HEADER TANGGAL (1 - 31) ---
        $colIndex = 3; // Mulai dari Kolom C

        for ($d = 1; $d <= $data['jumlahHari']; $d++) {
            $date = Carbon::createFromDate($data['tahun'], $data['bulan'], $d);

            // Kita butuh 2 kolom per tanggal (Masuk & Pulang)
            // Kolom Pertama (Start)
            $colStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            // Kolom Kedua (End)
            $colEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

            // 1. Baris 4: Tanggal (Merged 2 Kolom)
            $sheet->setCellValue($colStart . '4', $d);
            $sheet->mergeCells($colStart . '4:' . $colEnd . '4');

            // 2. Baris 5: Hari (Merged 2 Kolom)
            $sheet->setCellValue($colStart . '5', substr($date->translatedFormat('D'), 0, 1)); // S, M, R...
            $sheet->mergeCells($colStart . '5:' . $colEnd . '5');

            // 3. Baris 6: Keterangan (M & P)
            $sheet->setCellValue($colStart . '6', 'Masuk'); // Masuk
            $sheet->setCellValue($colEnd . '6', 'Pulang');  // Pulang

            // Styling Header Hari Libur / Minggu
            $isLibur = $date->dayOfWeek == $profil->hari_libur_mingguan || in_array($date->format('Y-m-d'), $data['libur']);

            if ($isLibur) {
                // Warnai header dan nanti kolom ke bawah merah muda/kuning
                $sheet->getStyle($colStart . '4:' . $colEnd . '100')
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('YYFFCCCC');
                // Font Tanggal Merah
                $sheet->getStyle($colStart . '4')->getFont()->getColor()->setARGB('FFFF0000');
            }

            // Geser index 2 langkah (karena pakai 2 kolom)
            $colIndex += 2;
        }

        // Header Rekap (H, S, I, A) - Ditaruh setelah semua tanggal
        $rekapHeaders = ['H', 'S', 'I', 'A'];
        foreach ($rekapHeaders as $rh) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . '4', $rh);
            $sheet->mergeCells($colLetter . '4:' . $colLetter . '6'); // Merge sampai baris 6
            $colIndex++;
        }

        // Merge Judul Utama agar rata tengah sepanjang tabel
        $lastColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        $sheet->mergeCells('A1:' . $lastColStr . '1');
        $sheet->mergeCells('A2:' . $lastColStr . '2');

        // --- B. ISI DATA ---
        $row = 7; // Data mulai baris ke-7

        foreach ($data['users'] as $index => $user) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $user->nama);

            // Reset Counter Statistik
            $h = 0;
            $s = 0;
            $i = 0;
            $a = 0;

            // Loop Tanggal untuk Data
            $colIndex = 3;
            for ($d = 1; $d <= $data['jumlahHari']; $d++) {
                $tglStr = sprintf('%s-%02d-%02d', $data['tahun'], $data['bulan'], $d);

                // Kolom Masuk & Pulang
                $colMasuk = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $colPulang = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

                $absen = $user->absensi_mapped[$d] ?? null;
                $isLibur = (Carbon::createFromDate($data['tahun'], $data['bulan'], $d)->dayOfWeek == $profil->hari_libur_mingguan) || in_array($tglStr, $data['libur']);

                // LOGIKA PENGISIAN CELL
                if ($isLibur) {
                    $sheet->setCellValue($colMasuk . $row, 'L');
                    $sheet->setCellValue($colPulang . $row, 'L');
                } elseif ($absen) {
                    $status = $absen->status;

                    if ($status == 'H') {
                        // Jika Hadir, Tampilkan Jam
                        // Asumsi di database ada field jam_masuk & jam_pulang. Sesuaikan formatnya.
                        $jamMasuk = $absen->jam_masuk ? Carbon::parse($absen->jam_masuk)->format('H:i') : '-';
                        $jamPulang = $absen->jam_pulang ? Carbon::parse($absen->jam_pulang)->format('H:i') : '-';

                        $sheet->setCellValue($colMasuk . $row, $jamMasuk);
                        $sheet->setCellValue($colPulang . $row, $jamPulang);
                        $h++;
                    } else {
                        // Jika S, I, A -> Tampilkan Kode Status di kedua kolom
                        $sheet->setCellValue($colMasuk . $row, $status);
                        $sheet->setCellValue($colPulang . $row, $status);

                        if ($status == 'S') $s++;
                        if ($status == 'I') $i++;
                        if ($status == 'A') $a++;
                    }

                    // Warna Cell Berdasarkan Status (Opsional, biar cantik)
                    $color = match ($status) {
                        'H' => 'FFCCFFCC', // Hijau
                        'S' => 'FFFFFFCC', // Kuning
                        'I' => 'FFCCCCFF', // Ungu
                        'A' => 'FFFFCCCC', // Merah
                        default => 'FFFFFFFF'
                    };
                    $sheet->getStyle($colMasuk . $row . ':' . $colPulang . $row)
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                } else {
                    // Tidak ada data & Bukan Libur = Alpha
                    if ($tglStr <= date('Y-m-d')) {
                        $sheet->setCellValue($colMasuk . $row, 'A');
                        $sheet->setCellValue($colPulang . $row, 'A');
                        $sheet->getStyle($colMasuk . $row . ':' . $colPulang . $row)
                            ->getFont()->getColor()->setARGB('FFFF0000'); // Text Merah
                        $a++;
                    }
                }

                $colIndex += 2; // Geser 2 kolom
            }

            // Isi Kolom Rekap (H, S, I, A)
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++) . $row, $h);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++) . $row, $s);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++) . $row, $i);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++) . $row, $a);

            $row++;
        }

        // --- C. STYLING FINAL ---
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        // Terapkan border & align center ke seluruh tabel
        $sheet->getStyle('A4:' . $lastColStr . ($row - 1))->applyFromArray($styleArray);

        // Nama Rata Kiri
        $sheet->getStyle('B7:B' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Auto Size Kolom
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // Atur Lebar Kolom Tanggal (Kecil saja)
        for ($c = 3; $c < $colIndex; $c++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setWidth(6);
        }

        // --- D. OUTPUT FILE ---
        $writer = new Xlsx($spreadsheet);
        $filename = 'Rekap_Absensi_Detail_' . $data['kategori'] . '_' . $bulanStr . '_' . $data['tahun'] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // 3. LOGIC PENGAMBILAN DATA (PRIVATE)
    // =========================================================================
    private function getDataBulanan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));
        $kategori = $request->kategori ?? 'siswa'; // 'siswa' atau 'guru'
        $kelasId = $request->kelas_id;

        // 1. UPDATE DATA (AUTO ALPHA)
        // Update Absensi H tapi tidak pulang pada tanggal < hari ini
        // Absensi::where('status', 'H')
        //     ->whereNull('jam_pulang')
        //     ->whereDate('tanggal', '<', Carbon::now()->format('Y-m-d'))
        //     ->update([
        //         'status' => 'A',
        //         'keterangan' => 'Lupa Absen Pulang (Auto System)'
        //     ]);

        // 2. QUERY USER
        if ($kategori == 'guru') {
            $query = Guru::where('status', 'aktif')->orderBy('nama');
        } else {
            $query = Siswa::where('status', 'aktif')->orderBy('nama');
            if ($kelasId) {
                $query->where('id_kelas', $kelasId);
            }
        }

        // Ambil Data User + Absensi Bulan Ini
        $users = $query->with(['absensi' => function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun);
        }])->get();

        // Mapping Absensi agar mudah diakses di View/Excel: $user->absensi_mapped[TANGGAL]
        foreach ($users as $user) {
            $map = [];
            foreach ($user->absensi as $absen) {
                $tgl = (int) Carbon::parse($absen->tanggal)->format('j');
                $map[$tgl] = $absen;
            }
            $user->absensi_mapped = $map;
        }

        // 3. DATA PENDUKUNG
        $jumlahHari = Carbon::createFromDate($tahun, $bulan)->daysInMonth;
        $liburDB = HariLibur::pluck('tanggal')->toArray(); // Ambil array tanggal libur
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        $profil = ProfilSekolah::first();

        // Cari Wali Kelas (Jika mode siswa & ada filter kelas)
        $waliKelas = null;
        if ($kategori == 'siswa' && $kelasId) {
            // Asumsi ada relasi kelas->wali_kelas->guru
            $kelasObj = Kelas::with('wali_kelas.guru')->find($kelasId);
            if ($kelasObj && $kelasObj->wali_kelas) {
                $waliKelas = $kelasObj->wali_kelas->guru; // Object Guru
            }
        }

        return [
            'users' => $users,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'kelasId' => $kelasId,
            'kelasList' => $kelasList,
            'jumlahHari' => $jumlahHari,
            'libur' => $liburDB,
            'profil' => $profil,
            'waliKelas' => $waliKelas,
            'hari_libur_mingguan' => $profil->hari_libur_mingguan,
        ];
    }
}
