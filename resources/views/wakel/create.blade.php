@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="bg-white dark:bg-[#181818] dark:text-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-white border-b pb-2">Tugaskan Wali Kelas</h2>

        <form action="{{ route('wakel.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="kelas_id">
                    Pilih Kelas
                </label>
                <select name="kelas_id" id="kelas_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                    @endforeach
                </select>
                @if($kelasList->isEmpty())
                    <p class="text-xs text-red-500 mt-1">Semua kelas sudah memiliki wali kelas. Silakan gunakan menu Ganti/Edit.</p>
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="guru_id">
                    Pilih Guru (Wali Kelas)
                </label>
                <select name="guru_id" id="guru_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach($guruList as $guru)
                        <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Simpan Tugas
                </button>
                <a href="{{ route('wakel.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-500 dark:text-[#A7A7A7] hover:text-gray-800 dark:text-white">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection