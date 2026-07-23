@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Hari Libur</h2>

    <form action="{{ route('hari-libur.update', $hariLibur->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white shadow rounded-lg p-6 space-y-5">
            {{-- Tanggal --}}
            <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" id="tanggal" 
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('tanggal') border-red-500 @enderror"
                       value="{{ old('tanggal', $hariLibur->tanggal->format('Y-m-d')) }}" required>
                @error('tanggal')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Keterangan --}}
            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan <span class="text-gray-400 text-xs">(opsional)</span></label>
                <input type="text" name="keterangan" id="keterangan" 
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('keterangan') border-red-500 @enderror"
                       value="{{ old('keterangan', $hariLibur->keterangan) }}" maxlength="100" placeholder="Misal: Hari Raya Idul Fitri">
                @error('keterangan')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" 
                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Perbarui
                </button>
                <a href="{{ route('hari-libur.index') }}" 
                   class="inline-flex items-center px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition">
                    Kembali
                </a>
            </div>
        </div>
    </form>
</div>
@endsection