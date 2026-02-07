<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
       
        DB::table('users')->insert([
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        echo "✅ Data Dummy Berhasil Dibuat!\n";
    }
}