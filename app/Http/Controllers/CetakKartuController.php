<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\ProfilSekolah;
use Illuminate\Http\Request;
use FPDF;

class CetakKartuController extends Controller
{
    // --- 1. PUBLIC METHOD: CETAK SISWA ---
    public function cetakSiswa()
    {


        // Ambil data Siswa
        $dataSiswa = Siswa::with('kelas')
            ->where('status', 'aktif')
            ->orderBy('nama', 'ASC')
            ->get();

        // Format data agar seragam saat dikirim ke fungsi cetak
        // Kita ubah jadi array objek standar
        $profil = ProfilSekolah::first();
        $listKartu = $dataSiswa->map(function ($siswa) use ($profil) {
            return (object) [
                'desain_kartu' => $profil->desain_kartu_siswa_depan,
                'nama' => $siswa->nama,
                'no_induk_label' => 'NIS/NISN',
                'no_induk' => $siswa->nis . ' / ' . $siswa->nisn,
                'baris_3_label' => 'TTL', // Label baris ke-3
                'baris_3_value' => $siswa->tempat_lahir . ', ' . date('d/m/Y', strtotime($siswa->tanggal_lahir)),
                'baris_4_label' => 'Kelas',
                'baris_4_value' => $siswa->kelas->nama_kelas ?? '-',
                'qr_code' => $siswa->nisn // Kode untuk cari gambar QR
            ];
        });

        return $this->prosesCetakPDF($listKartu, 'Kartu Pelajar');
    }

    // --- 2. PUBLIC METHOD: CETAK GURU (Nanti bisa dipakai) ---
    public function cetakGuru()
    {
        // Ambil data Guru (Contoh)
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
                'baris_4_label' => 'Mapel', // Guru tidak punya kelas, tapi Mapel
                'baris_4_value' => $guru->mata_pelajaran ?? '-',
                'qr_code' => $guru->nip // Pakai NIP untuk nama file QR
            ];
        });

        return $this->prosesCetakPDF($listKartu, 'Kartu Guru');
    }

    // --- 3. PRIVATE METHOD: INTINYA DISINI (Reusable) ---
    private function prosesCetakPDF($dataList, $judulFile)
    {
        // Setup Profil & PDF
        if ($dataList->isEmpty() || empty($dataList->first()->desain_kartu)) {
            $background_path = '';
        } else {
            $background_path = public_path('storage/' . ($dataList->first()->desain_kartu ?? ''));
        }

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // Config Ukuran
        $card_width = 85.6;
        $card_height = 54;
        $margin_x = 16;
        $margin_y = 7;
        $spacing_x = 5;
        $spacing_y = 5;

        // Hitung Grid
        $page_width = 210;
        $page_height = 297;
        $printable_width = $page_width - 2 * $margin_x;
        $columns = (int) floor(($printable_width + $spacing_x) / ($card_width + $spacing_x));
        if ($columns < 1) $columns = 1;
        $rows = (int) floor(($page_height - 2 * $margin_y + $spacing_y) / ($card_height + $spacing_y));
        if ($rows < 1) $rows = 1;
        $cards_per_page = $columns * $rows;

        $pdf->AddPage();
        $index = 0;

        if ($dataList->isEmpty()) {
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Tidak ada data untuk dicetak.', 0, 1, 'C');
            return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
        }

        foreach ($dataList as $data) {
            // Halaman Baru
            if ($index > 0 && $index % $cards_per_page == 0) {
                $pdf->AddPage();
            }

            // Koordinat X Y
            $pos_in_page = $index % $cards_per_page;
            $col = $pos_in_page % $columns;
            $row = (int) floor($pos_in_page / $columns);
            $x = $margin_x + $col * ($card_width + $spacing_x);
            $y = $margin_y + $row * ($card_height + $spacing_y);

            // 1. Gambar Background
            if (file_exists($background_path)) {
                $pdf->Image($background_path, $x, $y, $card_width, $card_height);
            } else {
                $pdf->Rect($x, $y, $card_width, $card_height);
            }

            // 2. Logic Nama (Pemendekan)
            $pdf->SetFont('Arial', 'B', 9);
            $name = $this->shortenName($data->nama); // Kita pindah logic nama ke fungsi kecil di bawah

            // 3. Render Teks (Dinamis sesuai data yang dikirim)
            $labelW = 12; // Sedikit dilebarkan
            $gap = 2;
            $valW = $card_width - 6 - $labelW - $gap;
            $lineH = 3;
            $startX = $x + 2;
            $curY = $y + 15;

            // Nama
            $pdf->SetXY($startX, $curY);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($labelW, $lineH, 'Nama', 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' ' . $name, 0, 1, 'L');

            $pdf->SetFont('Arial', '', 6.5);

            // Baris 2 (Induk)
            $curY += $lineH;
            $pdf->SetXY($startX, $curY);
            $pdf->Cell($labelW, $lineH, $data->no_induk_label, 0, 0, 'L'); // Label Dinamis
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' ' . $data->no_induk, 0, 1, 'L'); // Value Dinamis

            // Baris 3 (TTL / Jabatan)
            $curY += $lineH;
            $pdf->SetXY($startX, $curY);
            $pdf->Cell($labelW, $lineH, $data->baris_3_label, 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' ' . $data->baris_3_value, 0, 1, 'L');

            // Baris 4 (Kelas / Mapel)
            $curY += $lineH;
            $pdf->SetXY($startX, $curY);
            $pdf->Cell($labelW, $lineH, $data->baris_4_label, 0, 0, 'L');
            $pdf->Cell($gap, $lineH, ':', 0, 0, 'C');
            $pdf->Cell($valW, $lineH, ' ' . $data->baris_4_value, 0, 1, 'L');

            // 4. Render QR Code
            $qr_path = public_path('storage/qr/' . $data->qr_code . '.png');
            if (file_exists($qr_path)) {
                $pdf->Image($qr_path, $x + $card_width - 28, $y + ($card_height - 20) / 2, 20, 20);
            }

            $index++;
        }

        return response($pdf->Output('I', $judulFile . '.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    // Helper kecil untuk menyingkat nama
    private function shortenName($name)
    {
        $name = trim($name);
        if ($name === '') return '-';
        $words = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 2) {
            $first_two = array_slice($words, 0, 2);
            $rest = array_slice($words, 2);
            $abbreviated = array_map(fn($w) => mb_substr($w, 0, 1) . '.', $rest);
            return implode(' ', array_merge($first_two, $abbreviated));
        }
        return implode(' ', $words);
    }
}
