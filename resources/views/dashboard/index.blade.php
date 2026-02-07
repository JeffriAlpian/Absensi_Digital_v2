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
                                <td colspan="4" class="text-center py-4 text-gray-400">Belum ada aktivitas hari ini.</td>
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
