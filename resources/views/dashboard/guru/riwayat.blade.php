@extends('layouts.guru')

@section('title', 'Riwayat Absensi Saya')

@section('content')
<div class="container mx-auto px-4 py-6">
    
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Riwayat Kehadiran</h1>
                <p class="text-sm text-gray-500">Pantau data absensi harian Anda di sini.</p>
            </div>
            
            <form action="{{ route('dashboard.guru.riwayat.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}" 
                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md text-sm shadow-sm px-1 py-2 max-w-1/2">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" 
                        class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md text-sm shadow-sm px-1 py-2 max-w-1/2">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('dashboard.guru.riwayat.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <div class="text-xs text-gray-500 uppercase font-bold">Total Hadir</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['hadir'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-yellow-500">
            <div class="text-xs text-gray-500 uppercase font-bold">Terlambat</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['telat'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
            <div class="text-xs text-gray-500 uppercase font-bold">Izin / Sakit</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['izin'] ?? 0 }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
            <div class="text-xs text-gray-500 uppercase font-bold">Tanpa Ket.</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['alpha'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status Kehadiran</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($riwayat as $data)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($data->tanggal)->isoFormat('dddd, D MMMM Y') }}
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($data->jam_masuk)
                                <span class="text-sm text-gray-700 font-mono bg-gray-100 px-2 py-1 rounded">
                                    {{ $data->jam_masuk }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($data->jam_pulang)
                                <span class="text-sm text-gray-700 font-mono bg-gray-100 px-2 py-1 rounded">
                                    {{ $data->jam_pulang }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">Belum Pulang</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = 'bg-gray-100 text-gray-800'; // Default
                                if($data->status == 'Hadir') $statusClass = 'bg-green-100 text-green-800';
                                elseif($data->status == 'Telat') $statusClass = 'bg-yellow-100 text-yellow-800';
                                elseif($data->status == 'Izin' || $data->status == 'Sakit') $statusClass = 'bg-blue-100 text-blue-800';
                                elseif($data->status == 'Alpha') $statusClass = 'bg-red-100 text-red-800';
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ $data->status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($data->status == 'Telat' && $data->terlambat_menit > 0)
                                <span class="text-red-500 font-medium">Telat {{ $data->terlambat_menit }} menit</span>
                            @else
                                {{ $data->keterangan ?? '-' }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                            <i class="fas fa-calendar-times text-4xl mb-3 block"></i>
                            <span class="text-base">Tidak ada data absensi pada periode ini.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $riwayat->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection