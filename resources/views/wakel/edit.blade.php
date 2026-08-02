@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="bg-white dark:bg-[#181818] dark:text-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-white border-b pb-2">Ganti Wali Kelas</h2>

        <form action="{{ route('wakel.update', $wakel->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">
                    Kelas
                </label>
                <input type="text" value="{{ $wakel->kelas->nama_kelas }}" class="shadow border rounded w-full py-2 px-3 text-gray-500 dark:text-[#A7A7A7] bg-gray-100 dark:bg-[#282828] cursor-not-allowed leading-tight focus:outline-none" disabled>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="guru_id">
                    Pilih Guru (Wali Kelas Baru)
                </label>
                <select name="guru_id" id="guru_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach($guruList as $guru)
                        <option value="{{ $guru->id }}" @if($wakel->guru_id == $guru->id) selected @endif>
                            {{ $guru->nama }}
                        </option>
                    @endforeach
                </select>
                @error('guru_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Simpan Perubahan
                </button>
                <a href="{{ route('wakel.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-500 dark:text-[#A7A7A7] hover:text-gray-800 dark:text-white">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection