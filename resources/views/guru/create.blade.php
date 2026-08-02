@extends('layouts.app')

@section('title', 'Tambah Guru')

@section('content')
    <div class="bg-white dark:bg-[#181818] dark:text-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Tambah Guru Baru</h2>

        <form action="{{ route('guru.store') }}" method="POST">
            @csrf <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">NIP</label>
                    <input type="text" name="nip" class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2"
                        required>
                    @error('nip')
                        <div class="text-red-600 text-sm mt-1">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </div>
                    @enderror

                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama</label>
                    <input type="text" name="nama" class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Jabatan</label>
                    <input type="text" name="jabatan" class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir"
                        class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                        class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">No WA</label>
                    <input type="text" name="no_wa" class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password</label>
                    <input type="password" name="password" class="mt-1 block w-full border border-gray-300 dark:border-[#333333] rounded-md p-2" required>
                </div>
                
            </div>

            <div class="mt-6 flex gap-2">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
                <a href="{{ route('guru.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</a>
            </div>
        </form>
    </div>
@endsection
