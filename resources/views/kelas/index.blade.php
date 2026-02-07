@extends('layouts.app')

@section('title', 'Kelas')

@section('content')
    <div class="flex-1 p-6">

        {{-- Alert Success/Error (Sama seperti sebelumnya) --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm relative" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';"><i class="fa-solid fa-xmark text-green-500 cursor-pointer"></i></span>
            </div>
        @endif

        {{-- Logika Judul & Warna Header Form --}}
        @php
            $is_edit = isset($kelas_edit); // Cek apakah sedang mode edit
            $form_action = $is_edit ? route('kelas.update', $kelas_edit->id) : route('kelas.store');
            $card_title = $is_edit ? 'Edit Data Kelas' : 'Tambah Kelas Baru';
            $btn_text = $is_edit ? 'Update' : 'Tambah';
            $btn_color = $is_edit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700';
            $btn_icon = $is_edit ? 'fa-save' : 'fa-plus';
        @endphp

        <div class="bg-white p-6 rounded-lg shadow-md mb-6 scroll-mt-20">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    {{ $card_title }} 
                    @if($is_edit) <span class="text-sm font-normal text-gray-500">(Sedang mengedit: {{ $kelas_edit->nama_kelas }})</span> @endif
                </h2>
                
                {{-- Tombol Batal Edit (Hanya muncul jika sedang edit) --}}
                @if ($is_edit)
                    <a href="{{ route('kelas.index') }}" class="text-sm text-red-500 hover:underline">
                        <i class="fa-solid fa-times mr-1"></i> Batal Edit
                    </a>
                @endif
            </div>

            <form method="post" action="{{ $form_action }}" class="flex flex-wrap items-end gap-4 ">
                @csrf
                @if ($is_edit)
                    @method('PUT') {{-- Wajib ada untuk update --}}
                @endif
                
                {{-- INPUT NAMA KELAS --}}
                <div class="flex-grow">
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                    {{-- Value: Jika ada old input (validasi gagal), pakai old. Jika tidak, cek apakah mode edit? jika ya pakai data edit. jika tidak, kosong --}}
                    <input type="text" id="nama_kelas" name="nama_kelas"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Contoh: 7A, 8B" required 
                        value="{{ old('nama_kelas', $is_edit ? $kelas_edit->nama_kelas : '') }}">
                </div>
                
                {{-- INPUT JAM MASUK --}}
                <div class="flex-grow">
                    <label for="jam_masuk" class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                    <input type="time" id="jam_masuk" name="jam_masuk"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        required 
                        value="{{ old('jam_masuk', $is_edit ? $kelas_edit->jam_masuk : '') }}">
                </div>
                
                {{-- INPUT JAM PULANG --}}
                <div class="flex-grow">
                    <label for="jam_pulang" class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                    <input type="time" id="jam_pulang" name="jam_pulang"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        required 
                        value="{{ old('jam_pulang', $is_edit ? $kelas_edit->jam_pulang : '') }}">
                </div>

                <button type="submit"
                    class="{{ $btn_color }} text-white font-bold py-2 px-6 rounded focus:ring-2 focus:ring-offset-2 h-[38px] mb-[1px] transition-colors">
                    <i class="fa-solid {{ $btn_icon }} mr-2"></i> {{ $btn_text }}
                </button>
            </form>
        </div>

        {{-- Tabel Daftar Kelas --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Daftar Kelas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-1/12">No</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jam Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jam Pulang</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-1/5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $no = 1; @endphp
                        @if ($data_kelas->count() == 0)
                            <tr><td colspan='5' class='px-4 py-4 text-center text-gray-500'>Belum ada data kelas.</td></tr>
                        @else
                            @foreach ($data_kelas as $row)
                                <tr class="hover:bg-gray-50 {{ isset($kelas_edit) && $kelas_edit->id == $row->id ? 'bg-blue-50' : '' }}">
                                    <td class="px-4 py-2 text-center text-sm text-gray-700">{{ $no++ }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $row->nama_kelas }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row->jam_masuk }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row->jam_pulang }}</td>
                                    <td class="px-4 py-2 text-center text-sm flex justify-center gap-2">
                                        
                                        {{-- Tombol Edit (Kuning) --}}
                                        <a href="{{ route('kelas.edit', $row->id) }}" 
                                           class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1.5 px-3 rounded text-xs transition-colors">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>

                                        {{-- Tombol Hapus (Merah) --}}
                                        <form action="{{ route('kelas.destroy', $row->id) }}" method="POST" 
                                              onsubmit="return confirm('Yakin hapus kelas {{ $row->nama_kelas }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1.5 px-3 rounded text-xs transition-colors">
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
@endsection