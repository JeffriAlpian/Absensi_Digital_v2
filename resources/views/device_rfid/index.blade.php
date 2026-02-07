@extends('layouts.app')

@section('title', 'Data Device RFID')

@section('content')
    <div class="flex-1 p-6">

        {{-- Menampilkan Pesan Sukses/Error --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm relative"
                role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <i class="fa-solid fa-xmark text-green-500 cursor-pointer"></i>
                </span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                <p class="font-bold">Perhatian!</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- LOGIKA MODE EDIT/TAMBAH --}}
        @php
            $is_edit = isset($device_rfid_edit);
            $form_action = $is_edit ? route('device_rfid.update', $device_rfid_edit->id) : route('device_rfid.store');
            $card_title = $is_edit ? 'Edit Device RFID' : 'Tambah Device Baru';
            $btn_text = $is_edit ? 'Update' : 'Generate & Simpan';
            $btn_color = $is_edit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700';
            $btn_icon = $is_edit ? 'fa-save' : 'fa-microchip';
        @endphp

        {{-- FORM INPUT --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-6 scroll-mt-20">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $card_title }}
                    @if ($is_edit)
                        <span class="text-sm font-normal text-gray-500">(Edit: {{ $device_rfid_edit->rfid_name }})</span>
                    @endif
                </h2>

                @if ($is_edit)
                    <a href="{{ route('device_rfid.index') }}" class="text-sm text-red-500 hover:underline">
                        <i class="fa-solid fa-times mr-1"></i> Batal Edit
                    </a>
                @endif
            </div>

            <form method="post" action="{{ $form_action }}" class="flex flex-wrap items-center gap-4">
                @csrf
                @if ($is_edit)
                    @method('PUT')
                @endif

                <div class="flex-grow">
                    <label for="device_name" class="block text-sm font-medium text-gray-700">Nama Device</label>
                    <input type="text" id="device_name" name="device_name"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                        placeholder="Contoh: Gerbang Utama, Perpustakaan" required
                        value="{{ old('device_name', $is_edit ? $device_rfid_edit->device_name : '') }}">
                    @if (!$is_edit)
                        <p class="text-xs text-gray-500 mt-1">*API Key akan digenerate otomatis setelah disimpan.</p>
                    @endif
                </div>

                <button type="submit" name="simpan"
                    class="{{ $btn_color }} text-white font-bold py-2 px-4 rounded focus:ring-2 focus:ring-offset-2 h-[38px] mb-[1px] transition-colors">
                    <i class="fa-solid {{ $btn_icon }} mr-2"></i>{{ $btn_text }}
                </button>
            </form>
        </div>

        {{-- TABEL DATA --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Daftar Device RFID</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th
                                class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-1/12">
                                No</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-1/4">
                                Nama Device</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">API Key
                                (Token)</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-1/5">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $no = 1; @endphp
                        @if ($data_device_rfid->count() == 0)
                            <tr>
                                <td colspan='4' class='px-4 py-4 text-center text-gray-500'>Belum ada device terdaftar.
                                </td>
                            </tr>
                        @else
                            @foreach ($data_device_rfid as $row)
                                <tr
                                    class="hover:bg-gray-50 {{ isset($device_rfid_edit) && $device_rfid_edit->id == $row->id ? 'bg-blue-50' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">
                                        {{ $no++ }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $row->rfid_name }}</td>
                                    <td
                                        class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 bg-gray-50 rounded select-all">
                                        <div class="flex items-center gap-2">
                                            <code
                                                class="bg-gray-100 px-2 py-1 rounded text-gray-600 font-mono text-xs select-all border border-gray-200">
                                                {{ $row->api_key }}
                                            </code>

                                            <button type="button" onclick="copyToClipboard('{{ $row->api_key }}', this)"
                                                class="text-gray-400 hover:text-blue-600 transition-colors p-1 rounded focus:outline-none"
                                                title="Salin API Key">
                                                <i class="fa-regular fa-copy text-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">

                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('device_rfid.edit', $row->id) }}"
                                            class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-3 rounded text-xs transition-colors mr-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('device_rfid.destroy', $row->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Yakin hapus device {{ $row->device_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-1.5 px-3 rounded text-xs transition-colors">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text, button) {
            // Menyalin teks ke clipboard
            navigator.clipboard.writeText(text).then(() => {
                // 1. Simpan icon asli (fa-copy)
                const originalIcon = '<i class="fa-regular fa-copy text-lg"></i>';

                // 2. Ubah icon menjadi Ceklis Hijau (fa-check)
                button.innerHTML = '<i class="fa-solid fa-check text-green-600 text-lg"></i>';

                // 3. Kembalikan ke icon asli setelah 2 detik
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                }, 2000);

            }).catch(err => {
                console.error('Gagal menyalin: ', err);
                alert('Gagal menyalin API Key.');
            });
        }
    </script>

@endsection
