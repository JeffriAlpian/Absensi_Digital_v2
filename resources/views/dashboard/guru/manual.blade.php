@extends('layouts.guru')

@section('title', 'Input Manual Absensi')

@section('content')

    <div class="max-w-7xl mx-auto">

        {{-- Alert Messages --}}
        @if (session('message'))
            @php
                $msg = session('message');
                $isSuccess = str_contains($msg, '✅');
                $bgClass = $isSuccess
                    ? 'bg-green-100 border-green-400 text-green-700'
                    : 'bg-yellow-100 border-yellow-400 text-yellow-700';
            @endphp
            <div class="{{ $bgClass }} border-l-4 p-4 mb-6 rounded shadow-sm relative" role="alert">
                <p class="font-bold">{{ $isSuccess ? 'Berhasil' : 'Perhatian' }}</p>
                <p>{{ $msg }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">

            <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6">

                {{-- Form Filter Utama --}}
                <form method="GET" action="{{ route('dashboard.guru.manual.index') }}"
                    class="flex flex-wrap items-end gap-3 w-full md:w-auto">

                    {{-- Filter Tanggal --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggal }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="kelas_id"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 bg-white">
                            <option value="">Semua Kelas</option>
                            @foreach ($listKelas as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <select name="status"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 bg-white">
                            <option value="">-- Semua Status --</option>
                            <option value="belum_absen" {{ $statusFilter == 'belum_absen' ? 'selected' : '' }}>Belum Absen
                            </option>
                            <option value="H" {{ $statusFilter == 'H' ? 'selected' : '' }}>Hadir (H)</option>
                            <option value="S" {{ $statusFilter == 'S' ? 'selected' : '' }}>Sakit (S)</option>
                            <option value="I" {{ $statusFilter == 'I' ? 'selected' : '' }}>Izin (I)</option>
                            <option value="A" {{ $statusFilter == 'A' ? 'selected' : '' }}>Alpha (A)</option>
                        </select>
                    </div>


                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition shadow-sm">
                        🔍 Tampilkan
                    </button>
                </form>


            </div>

            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Header Dinamis --}}
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NISN</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kelas</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataList as $item)
                            @php
                                // Mengambil relasi absensi (asumsi nama relasi di model Siswa & Guru sama: 'absensi')
                                $absen = $item->absensi->first();
                                $status = $absen->status ?? '';
                                $ket = $absen->keterangan ?? '';
                                $rowClass = $absen ? 'bg-green-50' : 'hover:bg-gray-50';

                                // Menentukan ID dan Nama Field input
                                $inputNameId = 'siswa_id';
                            @endphp
                            <tr class="{{ $rowClass }} transition">
                                <form method="POST" action="{{ route('absensi.manual.storeManual') }}">
                                    @csrf
                                    {{-- Kirim Kategori ke Controller --}}
                                    <input type="hidden" name="kategori" value="siswa">
                                    <input type="hidden" name="siswa_id" value="{{ $item->id }}">
                                    {{-- ID Universal --}}
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                    <input type="hidden" name="kelas_filter" value="{{ $kelasId }}">

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $item->nis }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $item->nama }}
                                    </td>

                                    {{-- Kolom Dinamis (Kelas / Jabatan) --}}

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ optional($item->kelas)->nama_kelas }}
                                    </td>


                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="status"
                                            class="block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-1 bg-white">
                                            <option value="" disabled {{ $status == '' ? 'selected' : '' }}>-
                                            </option>
                                            <option value="H" {{ $status == 'H' ? 'selected' : '' }}>H</option>
                                            <option value="S" {{ $status == 'S' ? 'selected' : '' }}>S</option>
                                            <option value="I" {{ $status == 'I' ? 'selected' : '' }}>I</option>
                                            <option value="A" {{ $status == 'A' ? 'selected' : '' }}>A</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="keterangan" value="{{ $ket }}"
                                            class="block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-1 px-2"
                                            placeholder="Ket. tambahan">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white 
                                                                                                {{ $absen ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                            {{ $absen ? 'Update' : 'Simpan' }}
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    ❌ Tidak ada data Siswa ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection