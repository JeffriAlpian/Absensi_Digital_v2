@extends('layouts.guru') @section('title', 'Dashboard')

@section('content')
    <div class="bg-indigo-600 pt-8 pb-16 px-6 rounded-b-[2.5rem] relative z-10">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <p class="text-indigo-200 text-xs mb-1">Selamat Bertugas,</p>
                <h1 class="text-2xl font-bold">{{ $guru->nama }}</h1>
                <p><span class="text-sm"><i class="fa-solid fa-id-badge mr-1"></i>{{ $guru->nip }}</span></p>
                <p> {{ $guru->jabatan }} </p>
            </div>
            <div class="w-12 h-12 rounded-full border-2 border-indigo-400 overflow-hidden bg-white dark:bg-[#181818] dark:text-white">
                <img src="https://ui-avatars.com/api/?name={{ $guru->nama }}&background=random"
                    class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <div class="px-5 -mt-8 relative z-20">
        <div class="bg-white dark:bg-[#181818] dark:text-white rounded-2xl shadow-lg p-5 flex justify-between items-center">
            <div class="text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Hadir</p>
                <p class="text-xl font-bold text-green-800 mt-1"> {{$totalHadir}} </p>
            </div>
            <div class="w-px h-8 bg-gray-100 dark:bg-[#282828]"></div>
            <div class="text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Izin</p>
                <p class="text-xl font-bold text-yellow-500 mt-1"> {{ $totalIzin }} </p>
            </div>
            <div class="w-px h-8 bg-gray-100 dark:bg-[#282828]"></div>
            <div class="text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Sakit</p>
                <p class="text-xl font-bold text-amber-800 mt-1"> {{ $totalSakit }} </p>
            </div>
            <div class="w-px h-8 bg-gray-100 dark:bg-[#282828]"></div>
            <div class="text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Alpha</p>
                <p class="text-xl font-bold text-red-500 mt-1"> {{$totalAlpha}} </p>
            </div>
        </div>
    </div>

    <div class="px-5 mt-6 space-y-4">

        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm">Jadwal Hari Ini</h3>
            <a href="#" class="text-xs text-indigo-600">Lihat Semua</a>
        </div>

        <h2 class="text-center">Segera Hadir</h2>

        {{-- <div class="bg-white dark:bg-[#181818] dark:text-white p-4 rounded-xl border-l-4 border-indigo-500 shadow-sm flex items-center justify-between">
            <div>
                <p class="font-bold text-gray-800 dark:text-white text-sm">Matematika Wajib</p>
                <p class="text-xs text-gray-500 dark:text-[#A7A7A7] mt-1"><i class="fa-regular fa-clock mr-1"></i> 07:30 - 09:00</p>
            </div>
            <span class="bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-1 rounded">X-RPL 1</span>
        </div> --}}

        <h3 class="font-bold text-gray-800 dark:text-white text-sm mt-6 mb-2">Menu Lainnya</h3>
        <div class="grid grid-cols-4 gap-3">
            <a href="{{route('dashboard.guru.scan.index')}}" class="flex flex-col items-center gap-2">
                <div class="w-12 h-12 bg-white dark:bg-[#181818] dark:text-white rounded-xl shadow-sm flex items-center justify-center text-green-500">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span class="text-[10px] text-gray-500 dark:text-[#A7A7A7]">Scan</span>
            </a>
            <a href="{{route('dashboard.guru.manual.index')}}" class="flex flex-col items-center gap-2">
                <div class="w-12 h-12 bg-white dark:bg-[#181818] dark:text-white rounded-xl shadow-sm flex items-center justify-center text-blue-500">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <span class="text-[10px] text-gray-500 dark:text-[#A7A7A7]">Laporan</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2">
                <div class="w-12 h-12 bg-white dark:bg-[#181818] dark:text-white rounded-xl shadow-sm flex items-center justify-center text-purple-500">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <span class="text-[10px] text-gray-500 dark:text-[#A7A7A7]">Info</span>
            </a>
            
        </div>
    </div>
@endsection
