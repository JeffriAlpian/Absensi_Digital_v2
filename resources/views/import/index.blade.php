@extends('layouts.app')

@section('title' , 'Import Excel')

@section('content')
<div class="flex-1 p-6">

    @if(session('message'))
        @php
            $status = session('status', 'info');
            $bgClass = 'bg-blue-100 border-blue-400 text-blue-700';
            if($status == 'success') $bgClass = 'bg-green-100 border-green-400 text-green-700';
            if($status == 'warning') $bgClass = 'bg-yellow-100 border-yellow-400 text-yellow-700';
            if($status == 'error') $bgClass = 'bg-red-100 border-red-400 text-red-700';
        @endphp
        <div class="mb-6 p-4 {{ $bgClass }} border rounded relative" role="alert">
            <strong class="font-bold">{{ ucfirst($status) }}!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">
                <i class="fa-solid fa-user-graduate mr-2 text-green-600"></i>Import Siswa
            </h3>
            
            <form action="{{ route('import.excel.siswa') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Excel Siswa</label>
                    <input type="file" name="file_excel" required accept=".xlsx, .xls"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                </div>
                
                <div class="flex justify-between items-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">
                        <i class="fa-solid fa-upload mr-2"></i>Upload Siswa
                    </button>
                    <a href="{{ asset('assets/template_siswa.xlsx') }}" class="text-sm text-blue-600 hover:underline">
                        <i class="fa-solid fa-download mr-1"></i>Template Siswa
                    </a>
                </div>
            </form>
            
            <div class="mt-4 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                <p><strong>Kolom:</strong> NIS | NISN | Nama | Tempat Lahir | Tgl Lahir | Kelas | No WA</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">
                <i class="fa-solid fa-chalkboard-user mr-2 text-blue-600"></i>Import Guru
            </h3>
            
            <form action="{{ route('import.excel.guru') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Excel Guru</label>
                    <input type="file" name="file_excel" required accept=".xlsx, .xls"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                        <i class="fa-solid fa-upload mr-2"></i>Upload Guru
                    </button>
                    <a href="{{ asset('assets/template_guru.xlsx') }}" class="text-sm text-blue-600 hover:underline">
                        <i class="fa-solid fa-download mr-1"></i>Template Guru
                    </a>
                </div>
            </form>

            <div class="mt-4 text-xs text-gray-500 bg-gray-50 p-2 rounded">
                <p><strong>Kolom:</strong> NIP/NIK | Nama | Tempat Lahir | Tgl Lahir | Jabatan | No WA</p>
            </div>
        </div>
    </div>

    @if(session('import_errors'))
    <div class="mt-6 bg-red-50 p-4 rounded-lg border border-red-200">
        <h3 class="font-bold text-red-800 mb-2">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>Detail Error Import:
        </h3>
        <ul class="list-disc list-inside text-sm text-red-700 max-h-60 overflow-y-auto">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection