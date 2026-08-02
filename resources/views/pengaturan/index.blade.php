@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="flex-1 p-6" x-data="{ activeTab: 'profil' }">

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Pengaturan</h2>
                <p class="text-gray-600 dark:text-[#A7A7A7] text-sm">Kelola profil, sistem, dan database aplikasi.</p>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm relative">
                <pre class="whitespace-pre-wrap font-sans">{{ session('success') }}</pre>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer"
                    onclick="this.parentElement.style.display='none';">
                    <i class="fa-solid fa-xmark"></i>
                </span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm relative">
                {{ session('error') }}
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer"
                    onclick="this.parentElement.style.display='none';">
                    <i class="fa-solid fa-xmark"></i>
                </span>
            </div>
        @endif

        {{-- TAB NAVIGATION --}}
        <div class="bg-white dark:bg-[#181818] dark:text-white rounded-t-xl shadow-sm border-b border-gray-200 dark:border-[#282828]">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-[#A7A7A7]">
                <li class="mr-2">
                    <button @click="activeTab = 'profil'"
                        :class="activeTab === 'profil' ? 'text-blue-600 border-blue-600 bg-blue-50' :
                            'border-transparent hover:text-gray-600 dark:text-[#A7A7A7] hover:border-gray-300 dark:border-[#333333]'"
                        class="inline-block p-4 border-b-2 rounded-t-lg group transition-all flex items-center">
                        <i class="fa-solid fa-school mr-2 text-lg"></i> Profil Sekolah
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'backup'"
                        :class="activeTab === 'backup' ? 'text-blue-600 border-blue-600 bg-blue-50' :
                            'border-transparent hover:text-gray-600 dark:text-[#A7A7A7] hover:border-gray-300 dark:border-[#333333]'"
                        class="inline-block p-4 border-b-2 rounded-t-lg group transition-all flex items-center">
                        <i class="fa-solid fa-database mr-2 text-lg"></i> Backup & Restore
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'info'"
                        :class="activeTab === 'info' ? 'text-blue-600 border-blue-600 bg-blue-50' :
                            'border-transparent hover:text-gray-600 dark:text-[#A7A7A7] hover:border-gray-300 dark:border-[#333333]'"
                        class="inline-block p-4 border-b-2 rounded-t-lg group transition-all flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-lg"></i> Informasi Sistem
                    </button>
                </li>
                <li class="mr-2">
                    <button @click="activeTab = 'update'"
                        :class="activeTab === 'update' ? 'text-blue-600 border-blue-600 bg-blue-50' :
                            'border-transparent hover:text-gray-600 dark:text-[#A7A7A7] hover:border-gray-300 dark:border-[#333333]'"
                        class="inline-block p-4 border-b-2 rounded-t-lg group transition-all flex items-center">
                        <i class="fa-solid fa-cloud-arrow-down mr-2 text-lg"></i> Update Sistem
                    </button>
                </li>
            </ul>
        </div>

        {{-- TAB CONTENT CONTAINER --}}
        <div class="bg-white dark:bg-[#181818] dark:text-white p-8 rounded-b-xl shadow-md min-h-[500px]">

            {{-- 1. KONTEN: PROFIL SEKOLAH --}}
            <div x-show="activeTab === 'profil'" x-transition.opacity>
                <div class="border-b pb-4 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Edit Profil Sekolah</h3>
                    <p class="text-gray-500 dark:text-[#A7A7A7] text-sm">Data ini akan ditampilkan pada laporan dan kop surat.</p>
                </div>

                <form action="{{ route('pengaturan.updateProfil') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Logo Preview --}}
                        <div class="md:col-span-2 flex justify-center mb-4">
                            <div class="text-center">
                                @if ($profil->logo_sekolah)
                                    <img src="{{ asset('storage/' . $profil->logo_sekolah) }}"
                                        class="h-32 w-32 object-contain border rounded-lg shadow-sm mx-auto mb-2 bg-gray-50 dark:bg-[#121212]">
                                @else
                                    <div
                                        class="h-32 w-32 flex items-center justify-center bg-gray-100 dark:bg-[#282828] rounded-lg border-2 border-dashed border-gray-300 dark:border-[#333333] mx-auto mb-2">
                                        <span class="text-gray-400 text-xs">Belum ada logo</span>
                                    </div>
                                @endif
                                <label
                                    class="cursor-pointer bg-white dark:bg-[#181818] dark:text-white border border-gray-300 dark:border-[#333333] rounded-md py-1 px-3 text-sm font-medium hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212]">
                                    <span class="text-gray-700 dark:text-gray-200">Ganti Logo</span>
                                    <input type="file" name="logo_sekolah" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah"
                                value="{{ old('nama_sekolah', $profil->nama_sekolah) }}" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('alamat', $profil->alamat_sekolah) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nama Kepala Sekolah</label>
                            <input type="text" name="kepala_sekolah"
                                value="{{ old('kepala_sekolah', $profil->kepala_sekolah) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">NIP Kepala Sekolah</label>
                            <input type="text" name="nip_kepsek"
                                value="{{ old('nip_kepsek', $profil->nip_kepala_sekolah) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Api Key WA</label>
                            <input type="text" name="api_key_wa" value="{{ old('api_key_wa', $profil->key_wa_sidobe) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">WhatsApp Kepsek/Wakur (Penerima
                                Absen Guru)</label>
                            <input type="text" name="wakur_wa" value="{{ old('wakur_wa', $profil->wakur_wa) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Jam Masuk Guru</label>
                            <input type="time" name="jam_masuk_guru"
                                value="{{ old('jam_masuk_guru', $profil->jam_masuk_guru) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Jam Pulang Guru</label>
                            <input type="time" name="jam_pulang_guru"
                                value="{{ old('jam_pulang_guru', $profil->jam_pulang_guru) }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="grid gap-6 mb-3">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Libur Mingguan</label>
                                <select name="hari_libur_mingguan"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-[#333333] rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="0"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 0 ? 'selected' : '' }}>
                                        Minggu</option>

                                    <option value="1"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 1 ? 'selected' : '' }}>
                                        Senin</option>

                                    <option value="2"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 2 ? 'selected' : '' }}>
                                        Selasa</option>

                                    <option value="3"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 3 ? 'selected' : '' }}>
                                        Rabu</option>

                                    <option value="4"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 4 ? 'selected' : '' }}>
                                        Kamis</option>

                                    <option value="5"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 5 ? 'selected' : '' }}>
                                        Jum'at</option>

                                    <option value="6"
                                        {{ old('hari_libur_mingguan', $profil->hari_libur_mingguan) == 6 ? 'selected' : '' }}>
                                        Sabtu</option>
                                </select>
                            </div>


                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Latitude</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-map-pin text-gray-400"></i>
                                    </div>
                                    <input type="text" id="latitude" name="latitude" value="{{ $lat }}"
                                        readonly
                                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-[#333333] bg-gray-50 dark:bg-[#121212] text-gray-500 dark:text-[#A7A7A7] focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Longitude</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-map-location-dot text-gray-400"></i>
                                    </div>
                                    <input type="text" id="longitude" name="longitude" value="{{ $long }}"
                                        readonly
                                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-[#333333] bg-gray-50 dark:bg-[#121212] text-gray-500 dark:text-[#A7A7A7] focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Radius (Meter)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-circle-nodes text-gray-400"></i>
                                    </div>
                                    <input type="number" id="radius" name="radius" value="{{ $radius }}"
                                        oninput="updateCircleRadius()"
                                        class="pl-10 block w-full rounded-lg border-gray-300 dark:border-[#333333] focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-[#A7A7A7] mt-1">Jarak maksimal siswa bisa absen dari titik
                                    pusat.
                                </p>
                            </div>

                        </div>



                        <div class="mb-6 relative">
                            <div id="map" class="w-full h-[400px] rounded-xl border-2 border-gray-200 dark:border-[#282828] z-0">
                            </div>

                            <button type="button" onclick="getCurrentLocation()"
                                class="absolute top-4 right-4 z-[400] bg-white dark:bg-[#181818] dark:text-white text-indigo-600 px-4 py-2 rounded-lg shadow-md text-sm font-bold hover:bg-indigo-50 transition">
                                <i class="fa-solid fa-location-crosshairs mr-1"></i> Lokasi Saya
                            </button>
                        </div>





                        {{-- AREA DESAIN KARTU SISWA --}}
                        <div class="md:col-span-2 mt-8 pt-6 border-t border-gray-200 dark:border-[#282828]">
                            <div class="flex items-center mb-4">
                                <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                                    <i class="fa-solid fa-user-graduate text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">Desain Kartu Siswa</h4>
                                    <p class="text-xs text-gray-500 dark:text-[#A7A7A7]">Upload desain depan dan belakang untuk kartu pelajar.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-blue-50 p-6 rounded-xl border border-blue-100">
                                {{-- Siswa Depan --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 text-center">Tampak
                                        Depan</label>
                                    <div
                                        class="border-2 border-dashed border-blue-300 rounded-lg p-4 text-center bg-white dark:bg-[#181818] dark:text-white hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212] transition relative group">
                                        @if ($profil->desain_kartu_siswa_depan)
                                            <img src="{{ asset('storage/' . $profil->desain_kartu_siswa_depan) }}"
                                                class="h-40 mx-auto object-cover rounded shadow mb-2">
                                        @else
                                            <div
                                                class="h-40 w-full bg-gray-100 dark:bg-[#282828] flex items-center justify-center rounded mb-2 text-gray-400">
                                                <span class="text-xs">Kosong</span>
                                            </div>
                                        @endif
                                        <label class="cursor-pointer block">
                                            <span
                                                class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">Pilih
                                                File</span>
                                            <input type="file" name="desain_kartu_siswa_depan" class="hidden"
                                                accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <p class="text-[10px] text-gray-400 mt-1 file-name">Max 2MB (JPG/PNG)</p>
                                    </div>
                                </div>

                                {{-- Siswa Belakang --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 text-center">Tampak
                                        Belakang</label>
                                    <div
                                        class="border-2 border-dashed border-blue-300 rounded-lg p-4 text-center bg-white dark:bg-[#181818] dark:text-white hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212] transition relative">
                                        @if ($profil->desain_kartu_siswa_belakang)
                                            <img src="{{ asset('storage/' . $profil->desain_kartu_siswa_belakang) }}"
                                                class="h-40 mx-auto object-cover rounded shadow mb-2">
                                        @else
                                            <div
                                                class="h-40 w-full bg-gray-100 dark:bg-[#282828] flex items-center justify-center rounded mb-2 text-gray-400">
                                                <span class="text-xs">Kosong</span>
                                            </div>
                                        @endif
                                        <label class="cursor-pointer block">
                                            <span
                                                class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">Pilih
                                                File</span>
                                            <input type="file" name="desain_kartu_siswa_belakang" class="hidden"
                                                accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <p class="text-[10px] text-gray-400 mt-1 file-name">Max 2MB (JPG/PNG)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- AREA DESAIN KARTU GURU --}}
                        <div class="md:col-span-2 mt-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-teal-100 text-teal-600 p-2 rounded-lg mr-3">
                                    <i class="fa-solid fa-chalkboard-user text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">Desain Kartu Guru</h4>
                                    <p class="text-xs text-gray-500 dark:text-[#A7A7A7]">Upload desain depan dan belakang untuk kartu identitas
                                        guru.</p>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-teal-50 p-6 rounded-xl border border-teal-100">
                                {{-- Guru Depan --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 text-center">Tampak
                                        Depan</label>
                                    <div
                                        class="border-2 border-dashed border-teal-300 rounded-lg p-4 text-center bg-white dark:bg-[#181818] dark:text-white hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212] transition relative">
                                        @if ($profil->desain_kartu_guru_depan)
                                            <img src="{{ asset('storage/' . $profil->desain_kartu_guru_depan) }}"
                                                class="h-40 mx-auto object-cover rounded shadow mb-2">
                                        @else
                                            <div
                                                class="h-40 w-full bg-gray-100 dark:bg-[#282828] flex items-center justify-center rounded mb-2 text-gray-400">
                                                <span class="text-xs">Kosong</span>
                                            </div>
                                        @endif
                                        <label class="cursor-pointer block">
                                            <span
                                                class="bg-teal-600 text-white text-xs px-3 py-1 rounded hover:bg-teal-700">Pilih
                                                File</span>
                                            <input type="file" name="desain_kartu_guru_depan" class="hidden"
                                                accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <p class="text-[10px] text-gray-400 mt-1 file-name">Max 2MB (JPG/PNG)</p>
                                    </div>
                                </div>

                                {{-- Guru Belakang --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2 text-center">Tampak
                                        Belakang</label>
                                    <div
                                        class="border-2 border-dashed border-teal-300 rounded-lg p-4 text-center bg-white dark:bg-[#181818] dark:text-white hover:bg-gray-50 dark:hover:bg-[#282828] dark:bg-[#121212] transition relative">
                                        @if ($profil->desain_kartu_guru_belakang)
                                            <img src="{{ asset('storage/' . $profil->desain_kartu_guru_belakang) }}"
                                                class="h-40 mx-auto object-cover rounded shadow mb-2">
                                        @else
                                            <div
                                                class="h-40 w-full bg-gray-100 dark:bg-[#282828] flex items-center justify-center rounded mb-2 text-gray-400">
                                                <span class="text-xs">Kosong</span>
                                            </div>
                                        @endif
                                        <label class="cursor-pointer block">
                                            <span
                                                class="bg-teal-600 text-white text-xs px-3 py-1 rounded hover:bg-teal-700">Pilih
                                                File</span>
                                            <input type="file" name="desain_kartu_guru_belakang" class="hidden"
                                                accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <p class="text-[10px] text-gray-400 mt-1 file-name">Max 2MB (JPG/PNG)</p>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-colors">
                            <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. KONTEN: BACKUP & RESTORE --}}
            <div x-show="activeTab === 'backup'" style="display: none;" x-transition.opacity>
                <div class="flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Database Backup</h3>
                        <p class="text-gray-500 dark:text-[#A7A7A7] text-sm">Buat cadangan database secara berkala untuk keamanan data.</p>
                    </div>
                    <form action="{{ route('pengaturan.backup') }}" method="POST"
                        onsubmit="return confirm('Proses backup mungkin memakan waktu. Lanjutkan?');">
                        @csrf
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                            <i class="fa-solid fa-file-arrow-down mr-2"></i> Backup Database
                        </button>
                    </form>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Catatan Restore:</strong> Sistem ini hanya menyediakan fitur download file backup
                                (.sql/.zip).
                                Untuk melakukan restore, silakan import file tersebut secara manual melalui
                                <strong>phpMyAdmin</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden border border-gray-200 dark:border-[#282828] rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-[#282828]">
                        <thead class="bg-gray-50 dark:bg-[#121212]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-[#A7A7A7] uppercase">Nama File</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-[#A7A7A7] uppercase">Ukuran</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-[#A7A7A7] uppercase">Tanggal Dibuat
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-[#A7A7A7] uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#181818] dark:text-white divide-y divide-gray-200 dark:divide-[#282828]">
                            @forelse($backups as $backup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $backup['filename'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-[#A7A7A7]">{{ $backup['size'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-[#A7A7A7]">{{ $backup['date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('database.backup.download', ['filename' => $backup['filename']]) }}"
                                            class="text-blue-600 hover:text-blue-900 font-bold mx-2">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">
                                        <i class="fa-regular fa-folder-open text-2xl mb-2 block"></i>
                                        Belum ada file backup tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. KONTEN: INFORMASI SISTEM --}}
            <div x-show="activeTab === 'info'" style="display: none;" x-transition.opacity>
                <div class="border-b pb-4 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Informasi Server</h3>
                    <p class="text-gray-500 dark:text-[#A7A7A7] text-sm">Detail teknis lingkungan server aplikasi berjalan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($system_info as $key => $val)
                        <div class="bg-gray-50 dark:bg-[#121212] p-5 rounded-lg border border-gray-200 dark:border-[#282828] flex items-center justify-between">
                            <div>
                                <span
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $key }}</span>
                                <span
                                    class="block text-lg font-mono font-semibold text-gray-800 dark:text-white">{{ $val }}</span>
                            </div>
                            <div class="text-gray-300 text-2xl">
                                <i class="fa-solid fa-server"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. KONTEN: UPDATE SISTEM --}}
            <div x-show="activeTab === 'update'" style="display: none;" x-transition.opacity>
                <div class="border-b pb-4 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Update Sistem</h3>
                    <p class="text-gray-500 dark:text-[#A7A7A7] text-sm">Tarik pembaruan terbaru dari repositori (Git Pull).</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <i class="fa-brands fa-git-alt text-6xl text-orange-600 mb-4"></i>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Pembaruan Aplikasi</h4>
                    <p class="text-gray-600 dark:text-[#A7A7A7] mb-6 max-w-lg mx-auto">
                        Klik tombol di bawah ini untuk menarik kode terbaru dari server pusat (Git Repository).
                        Pastikan tidak ada perubahan lokal yang konflik.
                    </p>

                    <form action="{{ route('pengaturan.systemUpdate') }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin melakukan update sistem? Pastikan koneksi internet stabil.');">
                        @csrf
                        <button type="submit"
                            class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105">
                            <i class="fa-solid fa-sync fa-spin mr-2" style="--fa-animation-duration: 2s;"></i> Cek &
                            Lakukan Update
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>


    <script>
        // 1. Ambil Data Awal dari PHP
        var curLat = {{ $lat }};
        var curLng = {{ $long }};
        var curRadius = {{ $radius }};

        // 2. Inisialisasi Peta
        var map = L.map('map').setView([curLat, curLng], 16);

        // Layer Peta (Google Maps style)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // 3. Buat Marker yang BISA DIGESER (Draggable)
        var marker = L.marker([curLat, curLng], {
            draggable: true
        }).addTo(map);

        // 4. Buat Lingkaran Radius (Visualisasi)
        var circle = L.circle([curLat, curLng], {
            color: '#4F46E5', // Warna Indigo
            fillColor: '#818CF8',
            fillOpacity: 0.2,
            radius: curRadius
        }).addTo(map);

        // --- EVENT LISTENER ---

        // A. Saat Marker Selesai Digeser (Drag End)
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();

            // Update Input Form
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;

            // Pindahkan Lingkaran Radius ke posisi baru marker
            circle.setLatLng(position);

            // Pan map ke posisi baru
            map.panTo(position);
        });

        // B. Saat Peta Diklik (Alternatif selain geser marker)
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            // Pindahkan Marker & Lingkaran
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);

            // Update Input
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });

        // C. Update Radius saat Input Angka diubah
        function updateCircleRadius() {
            var newRadius = document.getElementById('radius').value;
            circle.setRadius(newRadius);
        }

        // D. Fitur "Lokasi Saya" (Geolocation Browser)
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;

                    // Pindahkan Marker, Lingkaran, dan Map View
                    marker.setLatLng([lat, lng]);
                    circle.setLatLng([lat, lng]);
                    map.setView([lat, lng], 18);

                    // Update Form
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                });
            } else {
                alert("Browser tidak mendukung Geolocation.");
            }
        }
    </script>
@endsection
