@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="flex-1 p-6">

    <a href="{{ route('siswa.index') }}" class="inline-flex items-center text-gray-600 dark:text-[#A7A7A7] hover:text-gray-900 dark:text-white mb-6 transition">
        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Data Siswa
    </a>

    <div class="bg-white dark:bg-[#181818] dark:text-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 border-b pb-2">
            <i class="fa-solid fa-user-pen mr-2 text-yellow-500"></i>Edit Data Siswa
        </h2>

        <form action="{{ route('siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data">
            @csrf 
            @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">NIS</label>
                    <input type="text" name="nis" 
                           value="{{ old('nis', $siswa->nis) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500 
                           @error('nis') border-red-500 ring-red-500 @else border-gray-300 dark:border-[#333333] @enderror"
                           placeholder="Nomor Induk Siswa">
                    
                    @error('nis')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">NISN</label>
                    <input type="number" name="nisn" 
                           value="{{ old('nisn', $siswa->nisn) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500
                           @error('nisn') border-red-500 ring-red-500 @else border-gray-300 dark:border-[#333333] @enderror"
                           placeholder="NIS Nasional">
                    
                    @error('nisn')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" 
                           value="{{ old('nama', $siswa->nama) }}" 
                           class="w-full border rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500
                           @error('nama') border-red-500 ring-red-500 @else border-gray-300 dark:border-[#333333] @enderror"
                           placeholder="Nama Siswa">
                    
                    @error('nama')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" 
                           value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" 
                           class="w-full border border-gray-300 dark:border-[#333333] rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" 
                           value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}" 
                           class="w-full border border-gray-300 dark:border-[#333333] rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kelas</label>
                    <select name="id_kelas" class="w-full border border-gray-300 dark:border-[#333333] rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" 
                                {{ old('id_kelas', $siswa->id_kelas) == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_kelas')
                        <div class="text-red-600 text-xs mt-1">Kelas wajib dipilih.</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">No. WhatsApp</label>
                    <input type="text" name="no_wa" 
                           value="{{ old('no_wa', $siswa->no_wa) }}" 
                           class="w-full border border-gray-300 dark:border-[#333333] rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Contoh: 62812345678">
                </div>

                <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Foto Siswa (Biarkan kosong jika tidak diubah)</label>
    <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 dark:border-[#333333] rounded-md px-3 py-2">
    @if($siswa->foto)
        <img src="{{ asset('storage/foto_siswa/' . $siswa->foto) }}" class="mt-2 w-20 h-20 object-cover rounded shadow">
    @endif
    @error('foto')
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
    @enderror
</div>

            </div>

            <div class="mt-8 flex gap-3 justify-end">
                <a href="{{ route('siswa.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition">
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