@extends('layouts.app')

@section('title', 'Kartu RFID')

@section('content')

    <div class="container mx-auto px-4 py-8">

        {{-- Header --}}
        <header class="bg-emerald-600 text-white p-6 rounded-xl shadow-md mb-6">
            <div class="flex items-center">
                <i class="fa-solid fa-id-card-clip text-3xl mr-4"></i>
                <div>
                    <h1 class="text-2xl font-bold">Registrasi Kartu RFID</h1>
                    <p class="opacity-90 mt-1">Daftarkan kartu RFID baru untuk Siswa dan Guru.</p>
                </div>
            </div>
        </header>

        {{-- Menampilkan Pesan Sukses/Error --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm relative"
                role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
                {{-- Tombol close sederhana dengan JS bawaan Alpine atau onclick --}}
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <i class="fa-solid fa-xmark text-green-500 cursor-pointer"></i>
                </span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm relative"
                role="alert">
                <p class="font-bold">Error!</p>
                <p>{{ session('error') }}</p>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                    <i class="fa-solid fa-xmark text-red-500 cursor-pointer"></i>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Form Registrasi Siswa --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col h-full">
                <div class="bg-blue-600 text-white px-6 py-4 flex items-center">
                    <i class="fa-solid fa-user-graduate mr-3 text-lg"></i>
                    <h5 class="text-lg font-semibold">Registrasi Siswa</h5>
                </div>
                <div class="p-6 flex-grow">
                    <form method="POST" action="{{ route('kartu.store') }}" id="formSiswa">
                        @csrf
                        <input type="hidden" name="tipe" value="siswa">

                        <div class="mb-5">
                            <label for="selectSiswa" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                                Siswa</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 bg-gray-50"
                                id="selectSiswa" name="user_id" required>
                                <option value="" selected disabled>... Pilih Nama Siswa ...</option>
                                @foreach ($siswa_belum_ada_kartu as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nisn }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="selectDevice" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                                Device</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 bg-gray-50"
                                id="selectDevice" name="device_id" required>
                                <option value="" selected disabled>... Pilih Device ...</option>
                                @foreach ($device_models as $d)
                                    <option value="{{ $d->id }}">{{ $d->rfid_name }}</option>
                                @endforeach
                                    
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="rfidSiswa" class="block text-sm font-medium text-gray-700 mb-2">Tempelkan Kartu
                                RFID</label>
                            <input type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-3 px-4 text-lg bg-gray-50"
                                id="rfidSiswa" name="uid" placeholder="Scan Kartu..." required autocomplete="off">
                        </div>

                        <div class="mt-auto">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex justify-center items-center shadow-md hover:shadow-lg">
                                <i class="fa-solid fa-save mr-2"></i> Simpan Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Registrasi Guru --}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col h-full">
                <div class="bg-teal-500 text-white px-6 py-4 flex items-center">
                    <i class="fa-solid fa-chalkboard-user mr-3 text-lg"></i>
                    <h5 class="text-lg font-semibold">Registrasi Guru</h5>
                </div>
                <div class="p-6 flex-grow">
                    <form method="POST" action="{{ route('kartu.store') }}" id="formGuru">
                        @csrf
                        <input type="hidden" name="tipe" value="guru">

                        <div class="mb-5">
                            <label for="selectGuru" class="block text-sm font-medium text-gray-700 mb-2">Pilih Guru</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 py-2 px-3 bg-gray-50"
                                id="selectGuru" name="user_id" required>
                                <option value="" selected disabled>... Pilih Nama Guru ...</option>
                                @foreach ($guru_belum_ada_kartu as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }} ({{ $g->nip ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="selectDevice" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                                Device</label>
                            <select
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 py-2 px-3 bg-gray-50"
                                id="selectDevice" name="device_id" required>
                                <option value="" selected disabled>... Pilih Device ...</option>
                                @foreach ($device_models as $d)
                                    <option value="{{ $d->id }}">{{ $d->rfid_name }}</option>
                                @endforeach
                                    
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="rfidGuru" class="block text-sm font-medium text-gray-700 mb-2">Tempelkan Kartu
                                RFID</label>
                            <input type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 py-3 px-4 text-lg bg-gray-50"
                                id="rfidGuru" name="uid" placeholder="Scan Kartu..." required autocomplete="off">
                        </div>

                        <div class="mt-auto">
                            <button type="submit"
                                class="w-full bg-teal-500 hover:bg-teal-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex justify-center items-center shadow-md hover:shadow-lg">
                                <i class="fa-solid fa-save mr-2"></i> Simpan Guru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Kartu Terdaftar --}}
    <div class="bg-gray-50 py-10 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <header class="mb-6 border-b border-gray-200 pb-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fa-solid fa-list mr-3 text-gray-500"></i> Daftar Kartu RFID Terdaftar
                </h2>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Tabel Siswa --}}
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gray-700 text-white px-6 py-3">
                        <h5 class="font-semibold">Kartu RFID Siswa</h5>
                    </div>
                    <div class="overflow-x-auto">
                        @if ($kartu_siswa->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Nama</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            UID</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($kartu_siswa as $ks)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $ks->siswa->nama ?? 'Siswa Terhapus' }}</div>
                                                <div class="text-sm text-gray-500">{{ $ks->siswa->nisn ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800 border border-gray-300">
                                                    {{ $ks->uid }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" onclick="openEditModal({{ $ks->id }}, '{{ $ks->uid }}')" class="text-blue-500 hover:text-blue-700 transition-colors mr-3" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                                </button>
                                                <form action="{{ route('kartu.destroy', $ks->id) }}" method="POST"
                                                    onsubmit="return confirm('Hapus kartu milik {{ $ks->siswa->nama ?? '' }}?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-500 hover:text-red-700 transition-colors"
                                                        title="Hapus">
                                                        <i class="fa-solid fa-trash-can text-lg"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-8 text-center text-gray-400 italic">Belum ada data siswa.</div>
                        @endif
                    </div>
                </div>

                {{-- Tabel Guru --}}
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gray-700 text-white px-6 py-3">
                        <h5 class="font-semibold">Kartu RFID Guru</h5>
                    </div>
                    <div class="overflow-x-auto">
                        @if ($kartu_guru->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Nama</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            UID</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($kartu_guru as $kg)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $kg->guru->nama ?? 'Guru Terhapus' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800 border border-gray-300">
                                                    {{ $kg->uid }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" onclick="openEditModal({{ $kg->id }}, '{{ $kg->uid }}')" class="text-blue-500 hover:text-blue-700 transition-colors mr-3" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                                </button>
                                                <form action="{{ route('kartu.destroy', $kg->id) }}" method="POST"
                                                    onsubmit="return confirm('Hapus kartu milik {{ $kg->guru->nama ?? '' }}?');"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-500 hover:text-red-700 transition-colors"
                                                        title="Hapus">
                                                        <i class="fa-solid fa-trash-can text-lg"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-8 text-center text-gray-400 italic">Belum ada data guru.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    {{-- Modal Edit UID --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
        <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <i class="fa-solid fa-id-card text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4 mb-2">Edit UID Kartu</h3>
                <form id="editForm" method="POST" action="" class="mt-4">
                    @csrf
                    @method('PUT')
                    <div class="mb-4 text-left">
                        <label for="edit_uid" class="block text-sm font-medium text-gray-700 mb-2">UID Kartu Baru</label>
                        <input type="text" id="edit_uid" name="uid" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2 px-3 bg-gray-50" required autocomplete="off">
                    </div>
                    <div class="mt-6 sm:flex sm:flex-row-reverse text-right">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Auto Focus Logic
            const selectSiswa = document.getElementById('selectSiswa');
            const rfidSiswa = document.getElementById('rfidSiswa');
            const selectGuru = document.getElementById('selectGuru');
            const rfidGuru = document.getElementById('rfidGuru');

            if (selectSiswa) {
                selectSiswa.addEventListener('change', () => rfidSiswa.focus());
            }
            if (selectGuru) {
                selectGuru.addEventListener('change', () => rfidGuru.focus());
            }
        });

        function openEditModal(id, uid) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('edit_uid').value = uid;
            document.getElementById('editForm').action = "{{ url('kartu') }}/" + id;
            setTimeout(() => {
                document.getElementById('edit_uid').focus();
            }, 100);
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>

@endsection
