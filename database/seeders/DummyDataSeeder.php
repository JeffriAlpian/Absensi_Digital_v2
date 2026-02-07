<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Profil Sekolah (Penting untuk Secret Key WA)
        DB::table('profil_sekolah')->insert([
            'nama_sekolah' => 'SMP Plus Darul Amal',
            'alamat_sekolah' => 'Jl. Contoh No. 1',
            'key_wa_sidobe' => 'CONTOH_SECRET_KEY_123', // Kunci sembarang dulu
            'jam_masuk_guru' => '07:00:00',
            'jam_pulang_guru' => '14:00:00',
        ]);

        // 2. Buat Device RFID (Penting untuk API Key)
        $deviceId = DB::table('rfid_model')->insertGetId([
            'rfid_name' => 'Gerbang Utama',
            'api_key' => 'abcdef123456', // <--- INI API KEY YANG KITA TES NANTI
            // 'status' => 'online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Buat Kelas
        $kelasId = DB::table('kelas')->insertGetId([
            'nama_kelas' => '7A',
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '13:00:00',
            'created_at' => now(),
        ]);

        // 4. Buat Data Siswa
        $siswaId = DB::table('siswa')->insertGetId([
            'nama' => 'Budi Santoso',
            'nisn' => '1234567890',
            'id_kelas' => $kelasId,
            'no_wa' => '081234567890', // Ganti dengan nomor WA kamu buat tes notif
            'status' => 'aktif',
            'created_at' => now(),
        ]);

        // 5. Daftarkan Kartu RFID Siswa
        DB::table('kartu_rfid')->insert([
            'uid' => '11223344', // <--- INI UID KARTU YANG KITA TES NANTI
            'siswa_id' => $siswaId,
            'guru_id' => null,
            // 'status' => 'aktif',
            'device_id' => $deviceId,
            'created_at' => now(),
        ]);

        DB::table('users')->insert([
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        echo "✅ Data Dummy Berhasil Dibuat!\n";
        echo "API Key Device: abcdef123456\n";
        echo "UID Kartu: 11223344\n";
    }
}