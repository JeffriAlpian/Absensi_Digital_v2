

@extends('layouts.app')

@section('title', 'Data guru')

@section('content')
    <div class="flex flex-col gap-4">

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('guru.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                <i class="fa-solid fa-plus mr-2"></i>Tambah guru
            </a>

            <form action="{{ route('guru.generate_akun') }}" method="POST"
                onsubmit="return confirm('Generate akun untuk semua guru?')">
                @csrf
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-bolt mr-2"></i>Generate Akun
                </button>
            </form>

            <a href="{{ route('cetak.kartu.guru') }}" target="_blank">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-id-card mr-2"></i>Cetak Semua Kartu
                </button>
            </a>

            <a href="{{ route('import.excel.form') }}">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-file-import mr-2"></i>Import dari Excel
                </button>
            </a>
        </div>


        <form method="GET" action="{{ route('guru.index') }}">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama, NIS, NISN..."
                    class="w-full pl-10 pr-3 py-2 border rounded-md focus:ring-green-500">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">NIP</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Jabatan</th>
                        <th class="p-3">QR Code</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_guru as $guru)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $guru->nip }}</td>
                            <td class="p-3">
                                <div class="font-bold">{{ $guru->nama }}</div>
                            </td>
                            <td class="p-3">{{ $guru->jabatan ?? '-' }}</td>
                            <td class="p-3">
                                <img src="{{ asset('storage/qr/' . $guru->nip . '.png') }}" alt="QR"
                                    class="w-10 h-10">
                            </td>
                            <td class="p-3 flex gap-2">
                                <a href="{{ route('guru.edit', $guru->id) }}"
                                    class="text-yellow-500 hover:text-yellow-600">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                <form action="{{ route('guru.keluar', $guru->id) }}" method="POST"
                                    onsubmit="return confirm('guru ini pansiun/keluar?')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-red-500 hover:text-red-600">
                                        <i class="fa-solid fa-user-minus"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
