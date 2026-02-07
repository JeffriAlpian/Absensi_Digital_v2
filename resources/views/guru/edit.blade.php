@extends('layouts.app')

@section('title', 'Edit Guru')

@section('content')
<div class="flex-1 p-6">

    <a href="{{ route('guru.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-6 transition">
        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Data Guru
    </a>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">
            <i class="fa-solid fa-user-pen mr-2 text-yellow-500"></i>Edit Data Guru
        </h2>

        <form action="{{ route('guru.update', $guru->id) }}" method="POST">
            @csrf 
            @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                    <input type="text" name="nip" 
                           value="{{ old('nip', $guru->nip) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500 
                           @error('nip') border-red-500 ring-red-500 @else border-gray-300 @enderror"
                           placeholder="Nomor Induk Pegawai">
                    
                    @error('nip')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" 
                           value="{{ old('nama', $guru->nama) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500
                           @error('nama') border-red-500 ring-red-500 @else border-gray-300 @enderror"
                           placeholder="Nama Guru">
                    
                    @error('nama')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" 
                           value="{{ old('jabatan', $guru->jabatan) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500
                           @error('jabatan') border-red-500 ring-red-500 @else border-gray-300 @enderror"
                           placeholder="Jabatan Guru">
                    
                    @error('jabatan')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" 
                           value="{{ old('tempat_lahir', $guru->tempat_lahir) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" 
                           value="{{ old('tanggal_lahir', $guru->tanggal_lahir) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                    <input type="text" name="no_wa" 
                           value="{{ old('no_wa', $guru->no_wa) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Contoh: 62812345678">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500
                           @error('password') border-red-500 ring-red-500 @else border-gray-300 @enderror"
                           placeholder="Isi jika ingin mengganti password">
                    
                    @error('password')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror

            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="{{ route('guru.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md shadow transition">
                    <i class="fa-solid fa-save mr-2"></i> Update Data
                </button>
            </div>

        </form>
    </div>
</div>
@endsection