@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Flash Message: Success --}}
        @if (session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Flash Message: Errors --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada inputan anda:</h3>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- KOLOM KIRI: Kartu Profil --}}
            <div class="xl:col-span-1">
                <div class="bg-white dark:bg-[#181818] dark:text-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 dark:bg-[#121212] border-b border-gray-200 dark:border-[#282828]">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Foto Profil
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6 text-center">
                        <img class="h-36 w-36 rounded-full mx-auto object-cover border-4 border-white shadow-lg mb-4"
                             src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? $user->username) }}&background=random&size=128"
                             alt="Profile Avatar">

                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name ?? $user->username }}</h4>
                        <p class="text-sm font-medium text-gray-500 dark:text-[#A7A7A7] uppercase tracking-wide">{{ ucfirst($user->role) }}</p>

                        <div class="mt-6 border-t border-gray-100 dark:border-[#282828] pt-4 text-left">
                            <div class="mb-3">
                                <span class="block text-xs font-medium text-gray-400 uppercase">Username</span>
                                <span class="block text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $user->username }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Form Edit --}}
            <div class="xl:col-span-2">
                <div class="bg-white dark:bg-[#181818] dark:text-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 dark:bg-[#121212] border-b border-gray-200 dark:border-[#282828]">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Edit Profil & Keamanan
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-8">
                                <h6 class="text-sm text-gray-400 font-bold uppercase tracking-wider mb-4">Informasi User</h6>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="input-username" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Username</label>
                                        <input type="text" name="username" id="input-username" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-[#333333] rounded-md p-2 border"
                                               placeholder="Username" 
                                               value="{{ old('username', $user->username) }}" required>
                                    </div>
                                    {{-- <div>
                                        <label for="input-name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Lengkap</label>
                                        <input type="text" name="name" id="input-name" ... >
                                    </div> --}}
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-[#282828] my-6">

                            <div class="mb-6">
                                <h6 class="text-sm text-gray-400 font-bold uppercase tracking-wider mb-4">Ganti Password (Opsional)</h6>
                                
                                <div class="mb-4">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password Saat Ini</label>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-[#333333] rounded-md p-2 border"
                                           placeholder="Isi jika ingin ganti password">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-[#A7A7A7]">Kosongkan jika tidak ingin mengganti password.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Password Baru</label>
                                        <input type="password" name="new_password" id="new_password" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-[#333333] rounded-md p-2 border"
                                               placeholder="Password Baru">
                                    </div>
                                    <div>
                                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Konfirmasi Password</label>
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-[#333333] rounded-md p-2 border"
                                               placeholder="Ulangi Password Baru">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                    Simpan Perubahan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection