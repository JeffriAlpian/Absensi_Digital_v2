@extends('layouts.app')

@section('title', 'Rekap Harian')

@section('content')
    <div x-data="{ activeTab: 'guru' }" class="space-y-6">

        <div
            class="flex flex-col md:flex-row justify-between items-end bg-white dark:bg-[#181818] dark:text-white p-5 rounded-xl shadow-sm border border-gray-100 dark:border-[#282828] gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Rekap Absensi Harian</h1>
                <p class="text-sm text-gray-500 dark:text-[#A7A7A7]">{{ $formattedDate }}</p>
            </div>

            <form action="{{ route('rekap.harian') }}" method="GET" class="flex flex-wrap gap-2 items-end">
                <div>
                    <label class="text-xs text-gray-400 font-bold ml-1">Tanggal</label>
                    <input type="date" name="date" value="{{ $tanggal }}"
                        class="block w-full border-gray-300 dark:border-[#333333] rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 bg-gray-50 dark:bg-[#121212]">
                </div>

                <div>
                    <label class="text-xs text-gray-400 font-bold ml-1">Kelas (Siswa)</label>
                    <select name="kelas_id" class="block w-full border-gray-300 dark:border-[#333333] rounded-lg text-sm px-3 py-2 bg-gray-50 dark:bg-[#121212]">
                        <option value="">Semua Kelas</option>
                        @foreach ($listKelas as $k)
                            <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-bold transition h-[38px]">
                    <i class="fa-solid fa-filter mr-1"></i> Tampilkan
                </button>
            </form>
        </div>

        <div class="flex space-x-2 border-b border-gray-200 dark:border-[#282828]">
            <button @click="activeTab = 'guru'"
                :class="activeTab === 'guru' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' :
                    'border-transparent text-gray-500 dark:text-[#A7A7A7] hover:text-gray-700 dark:text-gray-200 hover:border-gray-300 dark:border-[#333333]'"
                class="py-3 px-6 text-sm font-bold border-b-2 rounded-t-lg transition-colors duration-200 flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user"></i> Data Guru
                <span
                    class="bg-gray-200 dark:bg-[#333333] text-gray-600 dark:text-[#A7A7A7] text-[10px] px-2 py-0.5 rounded-full">{{ $statsGuru['total'] }}</span>
            </button>

            <button @click="activeTab = 'siswa'"
                :class="activeTab === 'siswa' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' :
                    'border-transparent text-gray-500 dark:text-[#A7A7A7] hover:text-gray-700 dark:text-gray-200 hover:border-gray-300 dark:border-[#333333]'"
                class="py-3 px-6 text-sm font-bold border-b-2 rounded-t-lg transition-colors duration-200 flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Data Siswa
                <span
                    class="bg-gray-200 dark:bg-[#333333] text-gray-600 dark:text-[#A7A7A7] text-[10px] px-2 py-0.5 rounded-full">{{ $statsSiswa['total'] }}</span>
            </button>
        </div>

        <div x-show="activeTab === 'guru'" x-transition.opacity class="space-y-6">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#181818] dark:text-white p-4 rounded-xl shadow-sm border-l-4 border-green-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $statsGuru['hadir'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-4 rounded-xl shadow-sm border-l-4 border-yellow-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $statsGuru['terlambat'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-4 rounded-xl shadow-sm border-l-4 border-red-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Alpha / Belum</p>
                    <p class="text-2xl font-bold text-red-600">{{ $statsGuru['alpha'] }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-[#181818] dark:text-white rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-[#282828]">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-[#A7A7A7]">
                        <thead class="text-xs text-gray-700 dark:text-gray-200 uppercase bg-gray-50 dark:bg-[#121212] border-b">
                            <tr>
                                <th class="px-6 py-3 w-10">No</th>
                                <th class="px-6 py-3">Nama Guru</th>
                                <th class="px-6 py-3 text-center">Masuk</th>
                                <th class="px-6 py-3 text-center">Pulang</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-center">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#282828]">
                            @forelse($rekapGuru as $index => $guru)
                                @php $absen = $guru->absensi->first(); @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212]">
                                    <td class="px-6 py-3">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 dark:text-white">{{ $guru->nama }}</span>
                                            <span class="text-xs text-gray-400">{{ $guru->nip ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-200">
                                        {{ $absen ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-200">
                                        {{ $absen && $absen->jam_pulang ? \Carbon\Carbon::parse($absen->jam_pulang)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if (!$absen)
                                            <span class="badge-red">Alpha</span>
                                        @elseif(str_contains(strtolower($absen->keterangan), 'terlambat'))
                                            <span class="badge-yellow">Terlambat</span>
                                        @else
                                            <span class="badge-green">Hadir</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if ($absen && $absen->latitude)
                                            <a href="http://maps.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}"
                                                target="_blank" class="text-indigo-600 hover:underline text-xs">Maps</a>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Tidak ada data guru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'siswa'" x-transition.opacity class="space-y-6" style="display: none;">

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="bg-white dark:bg-[#181818] dark:text-white p-3 rounded-xl shadow-sm border-b-4 border-green-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Hadir</p>
                    <p class="text-xl font-bold text-green-600">{{ $statsSiswa['hadir'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-3 rounded-xl shadow-sm border-b-4 border-blue-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Sakit</p>
                    <p class="text-xl font-bold text-blue-600">{{ $statsSiswa['sakit'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-3 rounded-xl shadow-sm border-b-4 border-purple-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Izin</p>
                    <p class="text-xl font-bold text-purple-600">{{ $statsSiswa['izin'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-3 rounded-xl shadow-sm border-b-4 border-red-500">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Alpha</p>
                    <p class="text-xl font-bold text-red-600">{{ $statsSiswa['alpha'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#181818] dark:text-white p-3 rounded-xl shadow-sm border-b-4 border-gray-400">
                    <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] uppercase font-bold">Belum Absen</p>
                    <p class="text-xl font-bold text-gray-600 dark:text-[#A7A7A7]">{{ $statsSiswa['belum'] }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-[#181818] dark:text-white rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-[#282828]">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-[#A7A7A7]">
                        <thead class="text-xs text-gray-700 dark:text-gray-200 uppercase bg-gray-50 dark:bg-[#121212] border-b">
                            <tr>
                                <th class="px-6 py-3 w-10">No</th>
                                <th class="px-6 py-3">Nama Siswa</th>
                                <th class="px-6 py-3">Kelas</th>
                                <th class="px-6 py-3 text-center">Jam_ Masuk</th>
                                <th class="px-6 py-3 text-center">Jam Pulang</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-center">Ket</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#282828]">
                            @forelse($rekapSiswa as $index => $siswa)
                                @php $absen = $siswa->absensi->first(); @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212]">
                                    <td class="px-6 py-3">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 dark:text-white">{{ $siswa->nama }}</span>
                                            <span class="text-xs text-gray-400">{{ $siswa->nis ?? $siswa->nisn }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-[#A7A7A7]">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-200">
                                        {{ $absen ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-center font-mono font-bold text-gray-700 dark:text-gray-200">
                                        {{ $absen && $absen->jam_pulang ? \Carbon\Carbon::parse($absen->jam_pulang)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if (!$absen)
                                            <span class="badge-gray">Belum</span>
                                        @elseif($absen->status == 'H')
                                            <span class="badge-green">Hadir</span>
                                        @elseif($absen->status == 'S')
                                            <span class="badge-blue">Sakit</span>
                                        @elseif($absen->status == 'I')
                                            <span class="badge-purple">Izin</span>
                                        @elseif($absen->status == 'A')
                                            <span class="badge-red">Alpha</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center text-xs text-gray-500 dark:text-[#A7A7A7]">
                                        {{ $absen->keterangan ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-400">
                                        @if (!$kelasId)
                                            <p>Silakan pilih <b>Kelas</b> terlebih dahulu untuk menampilkan siswa.</p>
                                        @else
                                            Tidak ada data siswa di kelas ini.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <style>
        .badge-green {
            @apply bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-200;
        }

        .badge-yellow {
            @apply bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded border border-yellow-200;
        }

        .badge-red {
            @apply bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-200;
        }

        .badge-blue {
            @apply bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-200;
        }

        .badge-purple {
            @apply bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded border border-purple-200;
        }

        .badge-gray {
            @apply bg-gray-100 dark:bg-[#282828] text-gray-600 dark:text-[#A7A7A7] text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200 dark:border-[#282828];
        }
    </style>

@endsection
