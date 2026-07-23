<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\ProfilSekolah;
use App\Models\Siswa;
use App\Models\WaliKelas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapController extends Controller
{
    public function rekapHarian(Request $request)
    {
        $tanggal = $request->date ?? Carbon::now()->format('Y-m-d');
        $kelasId = $request->kelas_id;

        $formattedDate = Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        $listKelas = Kelas::orderBy('nama_kelas')->get();

        // Cek apakah hari ini libur (nasional atau mingguan)
        $profil = ProfilSekolah::first();
        $isHariLibur = false;
        if ($profil) {
            $liburNasional = HariLibur::pluck('tanggal')
                ->map(fn($tgl) => $tgl instanceof Carbon ? $tgl->format('Y-m-d') : $tgl)
                ->toArray();
            $hariIni = Carbon::parse($tanggal);
            $isMinggu = $hariIni->dayOfWeek == $profil->hari_libur_mingguan;
            $isNasional = in_array($hariIni->format('Y-m-d'), $liburNasional);
            $isHariLibur = $isMinggu || $isNasional;
        }

        // ---- DATA GURU ----
        $rekapGuru = Guru::with(['absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        // Statistik Guru
        if ($isHariLibur) {
            // Saat libur, semua guru dianggap "Libur", tidak ada statistik alpha
            $statsGuru = [
                'total'     => $rekapGuru->count(),
                'hadir'     => 0,
                'terlambat' => 0,
                'alpha'     => 0,
                'libur'     => $rekapGuru->count(), // libur semua
            ];
        } else {
            $statsGuru = [
                'total'     => $rekapGuru->count(),
                'hadir'     => $rekapGuru->filter(fn($g) => $g->absensi->first() && $g->absensi->first()->status == 'H')->count(),
                'terlambat' => $rekapGuru->filter(fn($g) => $g->absensi->first() && str_contains($g->absensi->first()->keterangan ?? '', 'Terlambat'))->count(),
                'alpha'     => $rekapGuru->filter(fn($g) => !$g->absensi->first())->count(),
                'libur'     => 0,
            ];
        }

        // ---- DATA SISWA ----
        $querySiswa = Siswa::with(['kelas', 'absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
            ->where('status', 'aktif')
            ->orderBy('nama');

        if ($kelasId) {
            $querySiswa->where('id_kelas', $kelasId);
        }

        $rekapSiswa = $querySiswa->get();

        if ($isHariLibur) {
            $statsSiswa = [
                'total'  => $rekapSiswa->count(),
                'hadir'  => 0,
                'sakit'  => 0,
                'izin'   => 0,
                'alpha'  => 0,
                'belum'  => 0,
                'libur'  => $rekapSiswa->count(),
            ];
        } else {
            $statsSiswa = [
                'total'  => $rekapSiswa->count(),
                'hadir'  => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'H')->count(),
                'sakit'  => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'S')->count(),
                'izin'   => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'I')->count(),
                'alpha'  => $rekapSiswa->filter(fn($s) => $s->absensi->first() && $s->absensi->first()->status == 'A')->count(),
                'belum'  => $rekapSiswa->filter(fn($s) => !$s->absensi->first())->count(),
                'libur'  => 0,
            ];
        }

        return view('absensi.rekap.harian', compact(
            'rekapGuru',
            'statsGuru',
            'rekapSiswa',
            'statsSiswa',
            'tanggal',
            'formattedDate',
            'listKelas',
            'kelasId',
            'isHariLibur'   // kirim ke view agar bisa menampilkan badge "Libur"
        ));
    }

    public function rekapBulanan(Request $request)
    {
        $data = $this->getDataBulanan($request);
        return view('absensi.rekap.bulanan', $data);
    }

    public function exportBulananExcel(Request $request)
    {
        $profil = ProfilSekolah::first();
        $data = $this->getDataBulanan($request);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // --- HEADER ---
        $bulanStr = Carbon::createFromDate(null, $data['bulan'])->translatedFormat('F');
        $sheet->setCellValue('A1', 'REKAP ABSENSI '.strtoupper($data['kategori']));
        $sheet->setCellValue('A2', "Bulan: $bulanStr {$data['tahun']}");

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A4', 'NO');
        $sheet->mergeCells('A4:A6');
        $sheet->setCellValue('B4', 'NAMA');
        $sheet->mergeCells('B4:B6');

        $colIndex = 3; // kolom C

        for ($d = 1; $d <= $data['jumlahHari']; $d++) {
            $date = Carbon::createFromDate($data['tahun'], $data['bulan'], $d);
            $colStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $colEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

            $sheet->setCellValue($colStart.'4', $d);
            $sheet->mergeCells($colStart.'4:'.$colEnd.'4');

            $sheet->setCellValue($colStart.'5', substr($date->translatedFormat('D'), 0, 1));
            $sheet->mergeCells($colStart.'5:'.$colEnd.'5');

            $sheet->setCellValue($colStart.'6', 'Masuk');
            $sheet->setCellValue($colEnd.'6', 'Pulang');

            // Cek libur
            $isLibur = $date->dayOfWeek == $profil->hari_libur_mingguan
                       || in_array($date->format('Y-m-d'), $data['libur']);

            if ($isLibur) {
                // Warna header kolom (hanya untuk header, jangan full kolom)
                $sheet->getStyle($colStart.'4:'.$colEnd.'6')
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFCCCC');  // pink muda, sudah benar
                $sheet->getStyle($colStart.'4')->getFont()->getColor()->setARGB('FFFF0000');
            }

            $colIndex += 2;
        }

        // Header rekap H, S, I, A
        $rekapHeaders = ['H', 'S', 'I', 'A'];
        foreach ($rekapHeaders as $rh) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter.'4', $rh);
            $sheet->mergeCells($colLetter.'4:'.$colLetter.'6');
            $colIndex++;
        }

        $lastColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        $sheet->mergeCells('A1:'.$lastColStr.'1');
        $sheet->mergeCells('A2:'.$lastColStr.'2');

        // --- ISI DATA ---
        $row = 7;
        foreach ($data['users'] as $index => $user) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValue('B'.$row, $user->nama);

            $h = $s = $i = $a = 0;
            $colIndex = 3;

            for ($d = 1; $d <= $data['jumlahHari']; $d++) {
                $tglStr = sprintf('%s-%02d-%02d', $data['tahun'], $data['bulan'], $d);
                $colMasuk  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $colPulang = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

                $absen = $user->absensi_mapped[$d] ?? null;
                $isLibur = (Carbon::createFromDate($data['tahun'], $data['bulan'], $d)->dayOfWeek == $profil->hari_libur_mingguan)
                           || in_array($tglStr, $data['libur']);

                if ($isLibur) {
                    $sheet->setCellValue($colMasuk.$row, 'L');
                    $sheet->setCellValue($colPulang.$row, 'L');
                    // Warna latar libur (bisa berbeda)
                    $sheet->getStyle($colMasuk.$row.':'.$colPulang.$row)
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFCCCC');
                } elseif ($absen) {
                    $status = $absen->status;
                    if ($status == 'H') {
                        $jamMasuk  = $absen->jam_masuk ? Carbon::parse($absen->jam_masuk)->format('H:i') : '-';
                        $jamPulang = $absen->jam_pulang ? Carbon::parse($absen->jam_pulang)->format('H:i') : '-';
                        $sheet->setCellValue($colMasuk.$row, $jamMasuk);
                        $sheet->setCellValue($colPulang.$row, $jamPulang);
                        $h++;
                    } else {
                        $sheet->setCellValue($colMasuk.$row, $status);
                        $sheet->setCellValue($colPulang.$row, $status);
                        if ($status == 'S') $s++;
                        if ($status == 'I') $i++;
                        if ($status == 'A') $a++;
                    }

                    $color = match ($status) {
                        'H' => 'FFCCFFCC',
                        'S' => 'FFFFFFCC',
                        'I' => 'FFCCCCFF',
                        'A' => 'FFFFCCCC',
                        default => 'FFFFFFFF'
                    };
                    $sheet->getStyle($colMasuk.$row.':'.$colPulang.$row)
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($color);
                } else {
                    // Tidak ada data & bukan libur -> Alpha jika sudah lewat
                    if ($tglStr <= date('Y-m-d')) {
                        $sheet->setCellValue($colMasuk.$row, 'A');
                        $sheet->setCellValue($colPulang.$row, 'A');
                        $sheet->getStyle($colMasuk.$row.':'.$colPulang.$row)
                            ->getFont()->getColor()->setARGB('FFFF0000');
                        $a++;
                    }
                }

                $colIndex += 2;
            }

            // Isi kolom rekap
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++).$row, $h);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++).$row, $s);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++).$row, $i);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex++).$row, $a);

            $row++;
        }

        // --- STYLING FINAL ---
        $styleArray = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A4:'.$lastColStr.($row - 1))->applyFromArray($styleArray);
        $sheet->getStyle('B7:B'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Lebar kolom
        $sheet->getColumnDimension('B')->setAutoSize(true);
        for ($c = 3; $c < $colIndex; $c++) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c)
            )->setWidth(6);
        }

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = 'Rekap_Absensi_Detail_'.$data['kategori'].'_'.$bulanStr.'_'.$data['tahun'].'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.urlencode($filename).'"');
        $writer->save('php://output');
        exit;
    }

    private function getDataBulanan(Request $request)
    {
        $bulan    = (int) ($request->bulan ?? date('m'));
        $tahun    = (int) ($request->tahun ?? date('Y'));
        $kategori = $request->kategori ?? 'siswa';
        $kelasId  = $request->kelas_id;

        // Query user
        if ($kategori == 'guru') {
            $query = Guru::where('status', 'aktif')->orderBy('nama');
        } else {
            $query = Siswa::where('status', 'aktif')->orderBy('nama');
            if ($kelasId) {
                $query->where('id_kelas', $kelasId);
            }
        }

        $users = $query->with(['absensi' => function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }])->get();

        foreach ($users as $user) {
            $map = [];
            foreach ($user->absensi as $absen) {
                $tgl = (int) Carbon::parse($absen->tanggal)->format('j');
                $map[$tgl] = $absen;
            }
            $user->absensi_mapped = $map;
        }

        $jumlahHari = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        // ** Perbaikan: format tanggal libur jadi string Y-m-d **
        $liburDB = HariLibur::pluck('tanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : $tanggal;
            })
            ->toArray();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $profil    = ProfilSekolah::first();

        $waliKelas = null;
        if ($kategori == 'siswa' && $kelasId) {
            $waliKelas = WaliKelas::where('kelas_id', $kelasId)->first();
        }

        return [
            'users'       => $users,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'kategori'    => $kategori,
            'kelasId'     => $kelasId,
            'kelasList'   => $kelasList,
            'jumlahHari'  => $jumlahHari,
            'libur'       => $liburDB,              // sekarang array string
            'profil'      => $profil,
            'waliKelas'   => $waliKelas,
            'hari_libur_mingguan' => $profil->hari_libur_mingguan ?? 0,
        ];
    }
}