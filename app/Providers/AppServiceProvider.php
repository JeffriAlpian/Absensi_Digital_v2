<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\ProfilSekolah;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('profil_sekolah')) {
            $sekolah = ProfilSekolah::first();

            View::share('sekolah_info', $sekolah);
        }

        if (Schema::hasTable('siswa')) {
            $jumlahSiswa = \App\Models\Siswa::hitungSiswa();
            View::share('jumlah_siswa', $jumlahSiswa);
        }

        if (Schema::hasTable('guru')) {
            $jumlahGuru = \App\Models\Guru::hitungGuru();
            View::share('jumlah_guru', $jumlahGuru);
        }


    }
}
