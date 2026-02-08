@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_siswa'] }}</p>
                </div>
                <div class="bg-blue-50 text-blue-600 p-3 rounded-full">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Total Guru</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_guru'] }}</p>
                </div>
                <div class="bg-purple-50 text-purple-600 p-3 rounded-full">
                    <i class="fa-solid fa-chalkboard-user text-xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between border-l-4 border-green-500">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Hadir Hari Ini</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['hadir_hari_ini'] }}</p>
                </div>
                <div class="bg-green-50 text-green-600 p-3 rounded-full">
                    <i class="fa-solid fa-check text-xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between border-l-4 border-red-500">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Alpha / Belum</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['alpha_hari_ini'] }}</p>
                </div>
                <div class="bg-red-50 text-red-600 p-3 rounded-full">
                    <i class="fa-solid fa-user-xmark text-xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tren Kehadiran (7 Hari Terakhir)</h3>
                <div class="relative h-72">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Komposisi Hari Ini</h3>
                <div class="relative h-56 flex justify-center">
                    <canvas id="todayCompositionChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span> Hadir
                        ({{ $pieData['H'] }})</div>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400 mr-2"></span> Sakit
                        ({{ $pieData['S'] }})</div>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Izin
                        ({{ $pieData['I'] }})</div>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Alpha
                        ({{ $pieData['A'] }})</div>
                </div>
            </div>
        </div>

        <div class="p-6 bg-gray-50 min-h-screen">

            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">📡 WhatsApp Server Monitor</h1>
                    <p class="text-sm text-gray-500 mt-1">Pantau status pengiriman pesan real-time</p>
                </div>

                <button onclick="window.location.reload()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Refresh Data
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                <div class="bg-white overflow-hidden shadow-sm rounded-xl border-l-4 border-yellow-400">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Antrean (Pending)</dt>
                                    <dd>
                                        <div class="text-3xl font-bold text-gray-900">{{ $pendingCount }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-yellow-700 bg-yellow-50 rounded-md p-2 flex items-start">
                                <span class="mr-2">💡</span>
                                <span>Menunggu giliran dikirim oleh Worker. Jika angka ini menumpuk, cek apakah worker
                                    berjalan.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm rounded-xl border-l-4 {{ $failedCount > 0 ? 'border-red-500' : 'border-green-500' }}">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 {{ $failedCount > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-full p-3">
                                    @if ($failedCount > 0)
                                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    @else
                                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Gagal Kirim (Failed)</dt>
                                        <dd>
                                            <div class="text-3xl font-bold text-gray-900">{{ $failedCount }}</div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            @if ($failedCount > 0)
                                <div class="flex flex-col space-y-2">
                                    <form action="{{ route('wa.retry') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            🔄 Retry All
                                        </button>
                                    </form>

                                    <form action="{{ route('wa.flush') }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus semua log error? Data tidak bisa kembali.')">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                            🗑️ Hapus Log
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">📋 Daftar Antrean Terakhir</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID Job</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Percobaan</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Detail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">

                            {{-- 1. Loop Pending Jobs --}}
                            @foreach ($pendingJobs as $job)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            ⏳ Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        #{{ $job->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $job->attempts }}x
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 italic">
                                        Menunggu worker...
                                    </td>
                                </tr>
                            @endforeach

                            {{-- 2. Loop Failed Jobs --}}
                            @foreach ($failedJobs as $job)
                                <tr class="bg-red-50 hover:bg-red-100 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            ❌ Failed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        #{{ $job->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        -
                                    </td>
                                    <td class="px-6 py-4 text-sm text-red-600 max-w-xs truncate"
                                        title="{{ $job->exception }}">
                                        {{ Str::limit($job->exception, 50) }}
                                    </td>
                                </tr>
                            @endforeach

                            @if ($pendingJobs->isEmpty() && $failedJobs->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">Semua Bersih!</p>
                                            <p class="text-sm">Tidak ada antrean tertunda atau pesan gagal.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Aktivitas Absensi Terbaru</h3>
                <a href="{{ route('rekap.harian') }}" class="text-sm text-indigo-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Nama</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($latestActivities as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-gray-600">
                                    {{ \Carbon\Carbon::parse($log->updated_at)->format('H:i') }}
                                </td>
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ $log->siswa->nama ?? ($log->guru->nama ?? 'Unknown') }}
                                    <span class="text-xs text-gray-400 block">
                                        {{ $log->siswa ? 'Siswa' : 'Guru' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if ($log->status == 'H')
                                        <span
                                            class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded">Hadir</span>
                                    @elseif($log->status == 'S')
                                        <span
                                            class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-0.5 rounded">Sakit</span>
                                    @else
                                        <span
                                            class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded">{{ $log->status }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-400 text-xs">
                                    {{ $log->keterangan }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-400">Belum ada aktivitas hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // --- 1. CONFIG LINE CHART (TREN) ---
        const ctxTrend = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!}, // ['01 Feb', '02 Feb', ...]
                datasets: [{
                        label: 'Hadir',
                        data: {!! json_encode($chartDataHadir) !!},
                        borderColor: '#10B981', // Tailwind Green-500
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Terlambat',
                        data: {!! json_encode($chartDataTelat) !!},
                        borderColor: '#F59E0B', // Tailwind Yellow-500
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.3,
                        fill: true,
                        hidden: true // Default hidden biar gak penuh
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // --- 2. CONFIG DOUGHNUT CHART (KOMPOSISI) ---
        const ctxPie = document.getElementById('todayCompositionChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Sakit', 'Izin', 'Alpha'],
                datasets: [{
                    data: [
                        {{ $pieData['H'] }},
                        {{ $pieData['S'] }},
                        {{ $pieData['I'] }},
                        {{ $pieData['A'] }}
                    ],
                    backgroundColor: [
                        '#10B981', // Green
                        '#FACC15', // Yellow
                        '#3B82F6', // Blue
                        '#EF4444' // Red
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    } // Kita buat legend manual HTML biar rapi
                },
                cutout: '70%' // Biar bolong tengahnya besar (gaya modern)
            }
        });
    </script>
@endsection
