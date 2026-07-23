@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
    <div class="flex flex-col gap-4">

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-wrap gap-2 mb-2">
            <a href="{{ route('siswa.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                <i class="fa-solid fa-plus mr-2"></i>Tambah Siswa
            </a>

            <form action="{{ route('siswa.generate_akun') }}" method="POST"
                onsubmit="return confirm('Generate akun untuk semua siswa?')">
                @csrf
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-bolt mr-2"></i>Generate Akun
                </button>
            </form>

            <a href="{{ route('cetak.kartu.siswa') }}" target="_blank">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-id-card mr-2"></i>Cetak Semua Kartu
                </button>
            </a>

            <a href="{{ route('import.excel.form') }}">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">
                    <i class="fa-solid fa-file-import mr-2"></i>Import dari Excel
                </button>
            </a>

            {{-- TOMBOL KELAS (Muncul HANYA jika filter kelas sedang aktif) --}}
            @if(request('kelas_id'))
                <div class="border-l-2 border-gray-400 pl-2 ml-2 flex gap-2">
                    <a href="{{ route('cetak.kartu.siswa.kelas', request('kelas_id')) }}" target="_blank">
                        <button class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded shadow">
                            <i class="fa-solid fa-print mr-2"></i>Cetak Kartu Kelas Ini
                        </button>
                    </a>

                    <form action="{{ route('siswa.hapus_kelas', request('kelas_id')) }}" method="POST"
                        onsubmit="return confirm('PERINGATAN! Anda yakin ingin menghapus/mengeluarkan SEMUA siswa di kelas ini?')">
                        @csrf
                        <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded shadow">
                            <i class="fa-solid fa-trash-can mr-2"></i>Hapus Sekelas
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- FORM FILTER PENCARIAN & KELAS --}}
        <form method="GET" action="{{ route('siswa.index') }}" class="flex flex-col md:flex-row gap-2 mb-4">
            <select name="kelas_id" class="border rounded-md px-3 py-2 focus:ring-green-500 bg-white"
                onchange="this.form.submit()">
                <option value="">-- Semua Kelas --</option>
                @foreach ($kelas as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>

            <div class="relative flex-grow">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama, NIS, NISN..."
                    class="w-full pl-10 pr-3 py-2 border rounded-md focus:ring-green-500">
                <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <button type="submit"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md transition shadow">
                Filter
            </button>
        </form>

        {{-- FORM MASTER UNTUK BULK ACTION (CHECKBOX) --}}
        <form id="bulkForm" method="POST">
            @csrf

            {{-- Tombol Bulk Action --}}
            <div class="mb-3 flex gap-2">
                <button type="button" onclick="submitBulkAction('{{ route('cetak.kartu.siswa.banyak') }}', '_blank')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-sm shadow">
                    <i class="fa-solid fa-print mr-1"></i> Cetak Terpilih
                </button>
                <button type="button"
                    onclick="submitBulkAction('{{ route('siswa.hapus_banyak') }}', '_self', 'Yakin ingin menghapus/mengeluarkan semua siswa yang dipilih?')"
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-sm shadow">
                    <i class="fa-solid fa-trash mr-1"></i> Hapus Terpilih
                </button>
            </div>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 w-10 text-center">
                                <input type="checkbox" id="checkAll"
                                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            </th>

                            <th class="p-3">Foto</th>
                            <th class="p-3">NIS</th>
                            <th class="p-3">Nama</th>
                            <th class="p-3">Kelas</th>
                            <th class="p-3">QR Code</th>
                            <th class="p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data_siswa as $siswa)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $siswa->id }}"
                                        class="checkItem w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                </td>
                                <td class="p-3">
                                    @if($siswa->foto)
                                        <img src="{{ asset('storage/foto_siswa/' . $siswa->foto) }}" alt="Foto"
                                            class="w-10 h-10 object-cover rounded-full">
                                    @else
                                        <div
                                            class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs">
                                            Kosong</div>
                                    @endif
                                </td>
                                <td class="p-3">{{ $siswa->nis }}</td>
                                <td class="p-3">
                                    <div class="font-bold">{{ $siswa->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $siswa->nisn }}</div>
                                </td>
                                <td class="p-3">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td class="p-3">
                                    <img src="{{ asset('storage/qr/' . $siswa->nisn . '.png') }}" alt="QR" class="w-10 h-10">
                                </td>
                                <td class="p-3 flex gap-2">
                                    <a href="{{ route('siswa.edit', $siswa->id) }}"
                                        class="text-yellow-500 hover:text-yellow-600" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>

                                    <a href="#"
                                        onclick="event.preventDefault(); if(confirm('Siswa ini lulus/keluar?')) document.getElementById('delete-form-{{ $siswa->id }}').submit();"
                                        class="text-red-500 hover:text-red-600" title="Keluarkan">
                                        <i class="fa-solid fa-user-minus"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Form Hapus Individual (Tersembunyi di luar tabel utama agar tidak bentrok dengan form bulk) --}}
        @foreach ($data_siswa as $siswa)
            <form id="delete-form-{{ $siswa->id }}" action="{{ route('siswa.keluar', $siswa->id) }}" method="POST"
                class="hidden">
                @csrf @method('PUT')
            </form>
        @endforeach

    </div>

    {{-- SCRIPT UNTUK CHECKBOX & BULK ACTION --}}
    <script>
        // Fitur Check All
        document.getElementById('checkAll').addEventListener('change', function () {
            let checkboxes = document.querySelectorAll('.checkItem');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });

        // Fungsi Submit Bulk Form Dinamis
        function submitBulkAction(actionUrl, target = '_self', confirmMsg = null) {
            // Cek apakah ada checkbox yang dicentang
            let checkedItems = document.querySelectorAll('.checkItem:checked');
            if (checkedItems.length === 0) {
                alert('Pilih minimal satu data siswa!');
                return;
            }

            // Jika butuh konfirmasi (seperti hapus)
            if (confirmMsg) {
                if (!confirm(confirmMsg)) return;
            }

            // Ganti action dan target form, lalu submit
            let form = document.getElementById('bulkForm');
            form.action = actionUrl;
            form.target = target;
            form.submit();

            // Uncheck semua setelah disubmit (terutama berguna kalau target=_blank)
            setTimeout(() => {
                document.getElementById('checkAll').checked = false;
                document.querySelectorAll('.checkItem').forEach(cb => cb.checked = false);
            }, 1000);
        }
    </script>
@endsection