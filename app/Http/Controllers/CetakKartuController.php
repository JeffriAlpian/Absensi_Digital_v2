<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\ProfilSekolah;
use App\Models\Siswa;
use FPDF;
use Illuminate\Http\Request;

class CetakKartuController extends Controller
{
    // --- 1. PUBLIC METHOD: CETAK SISWA ---
    public function cetakSiswa()
    {
        $dataSiswa = Siswa::with('kelas')
            ->where('status', 'aktif')
            ->orderBy('nama', 'ASC')
            ->get();

        $profil = ProfilSekolah::first();
        $listKartu = $dataSiswa->map(function ($siswa) use ($profil) {
            return (object) [
                'desain_kartu' => $profil->desain_kartu_siswa_depan,
                'nama' => $siswa->nama,
                'no_induk_label' => 'NIS/NISN',
                'no_induk' => $siswa->nis.' / '.$siswa->nisn,
                
                // Pisahkan Tempat dan Tanggal Lahir menjadi dua baris
                'baris_3_label' => 'TTL',
                'baris_3_value' => $siswa->tempat_lahir.',', // Baris atas (Tempat Lahir)
                'baris_4_label' => '', // Label dikosongkan agar sejajar
                'baris_4_value' => date('d/m/Y', strtotime($siswa->tanggal_lahir)), // Baris bawah (Tanggal di-enter)
                
                'qr_code' => $siswa->nisn,
                'foto_path' => $siswa->foto ? public_path('storage/foto_siswa/' . $siswa->foto) : null,
            ];
        });

        return $this->prosesCetakPDF($listKartu, 'Kartu_Pelajar');
    }

    // --- 2. PUBLIC METHOD: CETAK GURU ---
    public function cetakGuru()
    {
        $dataGuru = Guru::orderBy('nama', 'ASC')->get();

        $profil = ProfilSekolah::first();
        $listKartu = $dataGuru->map(function ($guru) use ($profil) {
            return (object) [
                'desain_kartu' => $profil->desain_kartu_guru_depan,
                'nama' => $guru->nama,
                'no_induk_label' => 'NIP/NUPTK',
                'no_induk' => $guru->nip ?? '-',
                'baris_3_label' => 'Jabatan',
                'baris_3_value' => $guru->jabatan ?? 'Guru Mapel',
                'baris_4_label' => 'Mapel',
                'baris_4_value' => $guru->mata_pelajaran ?? '-',
                'qr_code' => $guru->nip,
                'foto_path' => null, 
            ];
        });

        return $this->prosesCetakPDF($listKartu, 'Kartu_Guru');
    }

    // --- 1.C PUBLIC METHOD: CETAK SISWA BANYAK (CHECKBOX) ---
    public function cetakSiswaBanyak(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return "<script>alert('Pilih minimal satu siswa!'); window.close();</script>";
        }

        $dataSiswa = Siswa::with('kelas')
            ->whereIn('id', $ids)
            ->where('status', 'aktif')
            ->orderBy('nama', 'ASC')
            ->get();

        $profil = ProfilSekolah::first();
        $listKartu = $dataSiswa->map(function ($siswa) use ($profil) {
            return (object) [
                'desain_kartu' => $profil->desain_kartu_siswa_depan,
                'nama' => $siswa->nama,
                'no_induk_label' => 'NIS/NISN',
                'no_induk' => $siswa->nis.' / '.$siswa->nisn,
                
                // Pisahkan Tempat dan Tanggal Lahir menjadi dua baris
                'baris_3_label' => 'TTL',
                'baris_3_value' => $siswa->tempat_lahir.',', 
                'baris_4_label' => '', 
                'baris_4_value' => date('d/m/Y', strtotime($siswa->tanggal_lahir)), 
                
                'qr_code' => $siswa->nisn,
                'foto_path' => $siswa->foto ? public_path('storage/foto_siswa/' . $siswa->foto) : null,
            ];
        });

        return $this->prosesCetakPDF($listKartu, 'Kartu_Pelajar_Terpilih');
    }

    // --- 1.B PUBLIC METHOD: CETAK SISWA PER KELAS ---
    public function cetakSiswaPerKelas($kelas_id)
    {
        $dataSiswa = Siswa::with('kelas')
            ->where('status', 'aktif')
            ->where('id_kelas', $kelas_id)
            ->orderBy('nama', 'ASC')
            ->get();

        if ($dataSiswa->isEmpty()) {
            return "<script>alert('Tidak ada data siswa di kelas ini!'); window.close();</script>";
        }

        $profil = ProfilSekolah::first();
        $listKartu = $dataSiswa->map(function ($siswa) use ($profil) {
            return (object) [
                'desain_kartu' => $profil->desain_kartu_siswa_depan,
                'nama' => $siswa->nama,
                'no_induk_label' => 'NIS/NISN',
                'no_induk' => $siswa->nis . ' / ' . $siswa->nisn,
                
                // Pisahkan Tempat dan Tanggal Lahir menjadi dua baris
                'baris_3_label' => 'TTL',
                'baris_3_value' => $siswa->tempat_lahir.',', 
                'baris_4_label' => '', 
                'baris_4_value' => date('d/m/Y', strtotime($siswa->tanggal_lahir)),
                
                'qr_code' => $siswa->nisn,
                'foto_path' => $siswa->foto ? public_path('storage/foto_siswa/' . $siswa->foto) : null,
            ];
        });

        $namaKelas = $dataSiswa->first()->kelas->nama_kelas ?? 'Kelas';
        $judulFile = 'Kartu_Pelajar_' . str_replace(' ', '_', $namaKelas);

        return $this->prosesCetakPDF($listKartu, $judulFile);
    }

    // --- 3. PRIVATE METHOD: INTINYA DISINI (Reusable) ---
   // --- 3. PRIVATE METHOD: INTINYA DISINI (Reusable) ---
    private function prosesCetakPDF($dataList, $judulFile)
    {
        if ($dataList->isEmpty() || empty($dataList->first()->desain_kartu)) {
            $background_path = '';
        } else {
            $background_path = public_path('storage/'.($dataList->first()->desain_kartu ?? ''));
        }

        // Setup Ukuran Kartu Standar CR80 (PVC)
        $card_width = 85.6;
        $card_height = 54;

        // Ubah orientasi menjadi L (Landscape), satuan mm, dan ukuran custom berupa array
        $pdf = new Fpdf('L', 'mm', array($card_width, $card_height));
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        if ($dataList->isEmpty()) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Tidak ada data.', 0, 1, 'C');
            return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
        }

        foreach ($dataList as $data) {
            // Langsung tambahkan halaman baru untuk setiap 1 data siswa/guru
            $pdf->AddPage();

            // Karena 1 halaman 1 kartu, titik X dan Y pasti selalu sejajar dari sudut 0,0
            $x = 0;
            $y = 0;

            // 1. Gambar Background
            if (file_exists($background_path)) {
                $pdf->Image($background_path, $x, $y, $card_width, $card_height);
            } else {
                $pdf->SetFillColor(255, 255, 255); // Background putih jika tidak ada desain
                $pdf->Rect($x, $y, $card_width, $card_height, 'F');
            }

            // 2. Logic Nama
            $name = $this->shortenName($data->nama);
            if ($judulFile == 'Kartu_Guru') {
                $name = $data->nama;
            }

            // --- 3. RENDER FOTO DI KIRI ---
            $fotoW = 15;
            $fotoH = 20;
            $fotoX = $x + 4;
            $fotoY = $y + 15;

            if (!empty($data->foto_path) && file_exists($data->foto_path)) {
                $pdf->Image($data->foto_path, $fotoX, $fotoY, $fotoW, $fotoH);
            } else {
                $pdf->SetDrawColor(200, 200, 200);
                $pdf->Rect($fotoX, $fotoY, $fotoW, $fotoH);
                $pdf->SetDrawColor(0, 0, 0);
            }

            // --- 4. RENDER TEKS BIODATA (Tengah) ---
            $labelW = 10;
            $gap = 1.5;
            $startX = $fotoX + $fotoW + 2; 
            $valW = $card_width - 32 - $labelW - $gap; 
            $lineH = 3.5;
            $curY = $y + 15;

            // Baris 1: Nama
            $pdf->SetXY($startX, $curY);
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->Cell($labelW, $lineH, 'Nama', 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' '.$name, 0, 1, 'L');

            $pdf->SetFont('Arial', '', 6.5);

            // Baris 2: Induk
            $curY += $lineH;
            $pdf->SetXY($startX, $curY);
            $pdf->Cell($labelW, $lineH, $data->no_induk_label, 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' '.$data->no_induk, 0, 1, 'L');

            // Baris 3: Tempat Lahir (Atau Jabatan pada Guru)
            $curY += $lineH;
            $pdf->SetXY($startX, $curY);
            $pdf->Cell($labelW, $lineH, $data->baris_3_label, 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' '.$data->baris_3_value, 0, 1, 'L');

            // Baris 4: Tanggal Lahir (Atau Mapel pada Guru)
            if (isset($data->baris_4_value)) {
                $curY += $lineH;
                $pdf->SetXY($startX, $curY);
                if ($data->baris_4_label !== '') {
                    $pdf->Cell($labelW, $lineH, $data->baris_4_label, 0, 0, 'L');
                    $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
                } else {
                    $pdf->Cell($labelW, $lineH, '', 0, 0, 'L');
                    $pdf->Cell($gap, $lineH, '', 0, 0, 'C');
                }
                $pdf->Cell($valW, $lineH, ' '.$data->baris_4_value, 0, 1, 'L');
            }

            // --- 5. RENDER QR CODE (Di Kanan) ---
            $qr_path = public_path('storage/qr/'.$data->qr_code.'.png');
            if (file_exists($qr_path)) {
                $pdf->Image($qr_path, $x + $card_width - 24, $y + ($card_height - 18) / 2, 18, 18);
            }
        }

        return response($pdf->Output('I', $judulFile.'.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    private function shortenName($name)
    {
        $name = trim($name);
        if ($name === '') return '-';
        
        $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 2) {
            $first_two = array_slice($words, 0, 2);
            $rest = array_slice($words, 2);
            $abbreviated = array_map(fn ($w) => mb_substr($w, 0, 1).'.', $rest);
            return implode(' ', array_merge($first_two, $abbreviated));
        }
        return implode(' ', $words);
    }
}