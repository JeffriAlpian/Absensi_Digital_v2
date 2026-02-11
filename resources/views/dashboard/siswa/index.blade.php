<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Siswa - {{ $siswa->nama ?? 'Siswa' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    {{-- HEADER --}}
    <header class="bg-gradient-to-r from-green-600 to-teal-600 text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fas fa-user-graduate text-xl"></i>
                <span class="text-lg font-semibold">Dashboard Siswa</span>
            </div>
            {{-- Pastikan route logout sesuai dengan web.php Anda --}}
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white rounded px-3 py-1.5 text-xs font-medium transition duration-200">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-6">

        {{-- PROFIL CARD --}}
        <div class="bg-white p-6 rounded-xl shadow-lg mb-6 border border-gray-200">
            <div class="flex flex-col sm:flex-row items-center gap-4">
                <div
                    class="w-16 h-16 rounded-full bg-gradient-to-br from-teal-400 to-green-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-sm">
                    {{ substr(strtoupper($siswa->nama), 0, 1) }}
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold text-gray-800">
                        Selamat Datang, {{ strtoupper($siswa->nama) }}!
                    </h2>
                    <p class="text-gray-600 text-md mt-1">
                        <i class="fas fa-chalkboard-teacher mr-1 text-green-600"></i> Kelas:
                        <span class="font-semibold">{{ $siswa->kelas->nama_kelas ?? 'N/A' }}</span>
                    </p>
                    <p class="text-gray-500 text-sm">
                        <i class="fas fa-id-card mr-1 text-green-600"></i> NISN:
                        <span class="font-semibold">{{ $siswa->nisn }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            {{-- SUMMARY CARDS --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                <h4 class="text-lg font-semibold mb-4 text-gray-700 flex items-center">
                    <i class="fas fa-calendar-check mr-2 text-indigo-600"></i>
                    Ringkasan Absensi Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {{-- Item Hadir --}}
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200 text-center">
                        <i class="fas fa-check-circle text-xl text-green-500 mb-1"></i>
                        <div class="text-3xl font-bold text-green-600">{{ $ringkasanBulanIni['H'] ?? 0 }}</div>
                        <div class="text-sm font-medium text-green-700">Hadir</div>
                    </div>
                    {{-- Item Sakit --}}
                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-center">
                        <i class="fas fa-notes-medical text-xl text-yellow-500 mb-1"></i>
                        <div class="text-3xl font-bold text-yellow-600">{{ $ringkasanBulanIni['S'] ?? 0 }}</div>
                        <div class="text-sm font-medium text-yellow-700">Sakit</div>
                    </div>
                    {{-- Item Izin --}}
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 text-center">
                        <i class="fas fa-info-circle text-xl text-blue-500 mb-1"></i>
                        <div class="text-3xl font-bold text-blue-600">{{ $ringkasanBulanIni['I'] ?? 0 }}</div>
                        <div class="text-sm font-medium text-blue-700">Izin</div>
                    </div>
                    {{-- Item Alpa --}}
                    <div class="p-4 bg-red-50 rounded-lg border border-red-200 text-center">
                        <i class="fas fa-times-circle text-xl text-red-500 mb-1"></i>
                        <div class="text-3xl font-bold text-red-600">{{ $ringkasanBulanIni['A'] ?? 0 }}</div>
                        <div class="text-sm font-medium text-red-700">Alpa</div>
                    </div>
                </div>
            </div>

            {{-- QR CODE SECTION --}}
            <div
                class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 flex flex-col items-center justify-center text-center">
                <h4 class="text-lg font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fas fa-qrcode mr-2 text-gray-600"></i> QR Code Absensi
                </h4>

                @php
                    // Logika Path Gambar yang Benar di Laravel
                    $qrPath = 'storage/qr/' . $nisn_siswa . '.png';
                    // Cek file fisik di folder public
                    $hasQr = file_exists(public_path($qrPath));
                    $qrUrl = $hasQr ? asset($qrPath) : 'https://via.placeholder.com/150?text=No+QR';
                @endphp

                <div class="p-2 border rounded-lg bg-gray-50 mb-3">
                    <img src="{{ $qrUrl }}?t={{ time() }}" alt="QR Code"
                        class="w-36 h-36 object-contain">
                </div>
                <p class="text-xs text-gray-500 mb-3">Gunakan kode ini untuk scan absensi.</p>

                @if ($hasQr)
                    <a href="{{ $qrUrl }}" download="QRCode-{{ $nisn_siswa }}.png"
                        class="bg-gray-700 hover:bg-gray-800 text-white rounded px-4 py-2 text-xs font-bold transition flex items-center">
                        <i class="fa-solid fa-download mr-1"></i> Unduh QR Code
                    </a>
                @endif
            </div>
        </div>

        {{-- RIWAYAT ABSENSI --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h4 class="text-lg font-semibold text-gray-700 flex items-center mb-2">
                    <i class="fas fa-history mr-2 text-purple-600"></i> Riwayat Kehadiran
                </h4>

                <p class="text-sm text-gray-500 mb-4 bg-yellow-50 p-2 rounded border border-yellow-200">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i>
                    Jika tidak melengkapi <strong>Absen Pulang</strong>, maka terhitung <span
                        class="text-red-600 font-bold">Alpha</span>.
                </p>

                {{-- FILTER FORM --}}
                <form id="filterForm" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="w-full sm:w-auto">
                        <label for="bulan" class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                        <select name="bulan" id="bulan"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-3 border">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}"
                                    {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <label for="tahun" class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                        <select name="tahun" id="tahun"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-3 border">
                            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}"
                                    {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    {{-- Tombol submit untuk jaga-jaga jika JS mati, disembunyikan jika JS aktif (opsional) --}}
                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-green-600 text-white px-4 py-1.5 rounded text-sm hover:bg-green-700">Filter</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Masuk</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Pulang</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Mapping warna status agar lebih rapi
                            $statusMap = [
                                'H' => [
                                    'label' => 'Hadir',
                                    'class' => 'text-green-600 bg-green-100',
                                    'icon' => 'fa-check-circle',
                                ],
                                'S' => [
                                    'label' => 'Sakit',
                                    'class' => 'text-yellow-600 bg-yellow-100',
                                    'icon' => 'fa-notes-medical',
                                ],
                                'I' => [
                                    'label' => 'Izin',
                                    'class' => 'text-blue-600 bg-blue-100',
                                    'icon' => 'fa-info-circle',
                                ],
                                'A' => [
                                    'label' => 'Alpa',
                                    'class' => 'text-red-600 bg-red-100',
                                    'icon' => 'fa-times-circle',
                                ],
                            ];
                        @endphp

                        @forelse($absensiList as $index => $row)
                            @php
                                // 1. Ambil status asli dari database
                                $kodeStatus = $row->status;

                                // 2. LOGIKA BARU:
                                // Jika statusnya Hadir (H) TAPI jam_pulang KOSONG, ubah jadi Alpa (A)
                                // (Kita hanya ubah visualnya di sini)
                                if ($kodeStatus == 'H' && empty($row->jam_pulang)) {
                                    $kodeStatus = 'A';
                                }

                                // 3. Ambil data tampilan berdasarkan kode status yang sudah diolah
                                $status = $statusMap[$kodeStatus] ?? [
                                    'label' => $kodeStatus,
                                    'class' => 'text-gray-600 bg-gray-100',
                                    'icon' => 'fa-question',
                                ];
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 text-center text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-gray-700">
                                    {{ $row->jam_masuk ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-gray-700">
                                    {{-- Tampilkan jam pulang, atau strip jika kosong --}}
                                    {{ $row->jam_pulang ? \Carbon\Carbon::parse($row->jam_pulang)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['class'] }}">
                                        <i class="fa-solid {{ $status['icon'] }} mr-1"></i> {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{-- Jika status diubah jadi A karena jam pulang kosong, beri keterangan --}}
                                    @if ($row->status == 'H' && empty($row->jam_pulang))
                                        <span class="text-red-500 text-xs italic">Lupa Absen Pulang</span>
                                    @else
                                        {{ $row->keterangan ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                        Tidak ada data absensi untuk periode ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-gray-800 text-white text-center py-4 mt-auto">
        <p class="text-sm">&copy; {{ date('Y') }} {{ $siswa->nama ?? 'Sekolah' }} - Aplikasi Absensi Digital</p>
    </footer>

    {{-- SCRIPT AUTO SUBMIT --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selects = document.querySelectorAll('#filterForm select');
            selects.forEach(select => {
                select.addEventListener('change', () => {
                    document.getElementById('filterForm').submit();
                });
            });
        });
    </script>
</body>

</html>
