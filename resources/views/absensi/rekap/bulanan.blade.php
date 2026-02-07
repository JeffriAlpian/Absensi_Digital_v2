@extends('layouts.app')
@section('title', 'Rekap Bulanan')

@section('content')
    <div class="space-y-6">

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <form action="{{ route('rekap.bulanan') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">

                <div class="w-full md:w-auto">
                    <label class="block text-xs font-bold text-gray-400 mb-1">Kategori</label>
                    <select name="kategori" class="p-2 w-full border-gray-300 rounded-lg text-sm bg-gray-50"
                        onchange="this.form.submit()">
                        <option value="siswa" {{ $kategori == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru" {{ $kategori == 'guru' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>

                @if ($kategori == 'siswa')
                    <div class="w-full md:w-auto">
                        <label class="block text-xs font-bold text-gray-400 mb-1">Kelas</label>
                        <select name="kelas_id" class="p-2 w-full border-gray-300 rounded-lg text-sm bg-gray-50">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 mb-1">Bulan</label>
                        <select name="bulan" class="p-2 border-gray-300 rounded-lg text-sm bg-gray-50">
                            @for ($b = 1; $b <= 12; $b++)
                                <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class=" block text-xs font-bold text-gray-400 mb-1">Tahun</label>
                        <input type="number" name="tahun" value="{{ $tahun }}"
                            class="p-2 border-gray-300 rounded-lg text-sm bg-gray-50">
                    </div>
                </div>

                <div class="flex gap-2 ml-auto">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition">
                        <i class="fa-solid fa-filter mr-1"></i> Tampilkan
                    </button>

                    <a href="{{ route('rekap.bulanan.export', request()->all()) }}" target="_blank"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center">
                        <i class="fa-solid fa-file-excel mr-2"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th rowspan="2" class="p-2 border border-gray-300 w-10">No</th>
                        <th rowspan="2" class="p-2 border border-gray-300 text-left min-w-[200px]">Nama
                            {{ ucfirst($kategori) }}</th>
                        <th colspan="{{ $jumlahHari }}" class="p-1 border border-gray-300 text-center">Tanggal
                            ({{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }})</th>
                        <th colspan="4" class="p-1 border border-gray-300 text-center bg-gray-200">Total</th>
                    </tr>
                    <tr>
                        @for ($i = 1; $i <= $jumlahHari; $i++)
                            @php
                                $dateLoop = \Carbon\Carbon::createFromDate($tahun, $bulan, $i);
                                $isLibur =
                                    $dateLoop->dayOfWeek == $hari_libur_mingguan ||
                                    in_array($dateLoop->format('Y-m-d'), $libur);
                            @endphp
                            <th
                                class="p-1 border border-gray-300 w-8 text-center {{ $isLibur ? 'bg-red-50 text-red-600' : '' }}">
                                {{ $i }} <br>
                                <span
                                    class="text-[9px] font-normal">{{ substr($dateLoop->translatedFormat('D'), 0, 1) }}</span>
                            </th>
                        @endfor
                        <th class="p-1 border border-gray-300 w-8 bg-green-100">H</th>
                        <th class="p-1 border border-gray-300 w-8 bg-yellow-100">S</th>
                        <th class="p-1 border border-gray-300 w-8 bg-blue-100">I</th>
                        <th class="p-1 border border-gray-300 w-8 bg-red-100">A</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600">
                    @forelse($users as $index => $user)
                        @php
                            $h = 0;
                            $s = 0;
                            $i = 0;
                            $a = 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-2 border border-gray-200 text-center">{{ $index + 1 }}</td>
                            <td class="p-2 border border-gray-200 font-medium text-gray-800">
                                {{ $user->nama }}
                                <div class="text-[10px] text-gray-400">{{ $user->nis ?? $user->nip }}</div>
                            </td>

                            @for ($d = 1; $d <= $jumlahHari; $d++)
                                @php
                                    $tglStr = sprintf('%s-%02d-%02d', $tahun, $bulan, $d);
                                    $isLibur =
                                        \Carbon\Carbon::createFromDate($tahun, $bulan, $d)->dayOfWeek ==
                                            $hari_libur_mingguan || in_array($tglStr, $libur);
                                    $absen = $user->absensi_mapped[$d] ?? null;
                                @endphp

                                @if ($isLibur)
                                    <td class="border border-gray-200 bg-gray-100 text-center text-gray-300 select-none">L
                                    </td>
                                @elseif($absen)
                                    @php
                                        if ($absen->status == 'H') {
                                            $h++;
                                            $bg = 'bg-green-50 text-green-700';
                                        } elseif ($absen->status == 'S') {
                                            $s++;
                                            $bg = 'bg-yellow-50 text-yellow-700';
                                        } elseif ($absen->status == 'I') {
                                            $i++;
                                            $bg = 'bg-blue-50 text-blue-700';
                                        } elseif ($absen->status == 'A') {
                                            $a++;
                                            $bg = 'bg-red-50 text-red-700';
                                        } else {
                                            $bg = '';
                                        }
                                    @endphp
                                    <td class="border border-gray-200 text-center font-bold {{ $bg }} cursor-help"
                                        title="{{ $absen->keterangan }}">
                                        {{ $absen->status }}
                                        @if (str_contains($absen->keterangan, 'Terlambat'))
                                            <sup class="text-red-500 font-bold text-[8px]">T</sup>
                                        @endif
                                    </td>
                                @else
                                    @if ($tglStr <= date('Y-m-d'))
                                        @php $a++; @endphp
                                        <td class="border border-gray-200 text-center font-bold text-red-600 bg-red-50">A
                                        </td>
                                    @else
                                        <td class="border border-gray-200 bg-white"></td>
                                    @endif
                                @endif
                            @endfor

                            <td class="border border-gray-200 text-center font-bold bg-green-50">{{ $h }}</td>
                            <td class="border border-gray-200 text-center font-bold bg-yellow-50">{{ $s }}</td>
                            <td class="border border-gray-200 text-center font-bold bg-blue-50">{{ $i }}</td>
                            <td class="border border-gray-200 text-center font-bold bg-red-50">{{ $a }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $jumlahHari + 6 }}" class="p-8 text-center text-gray-400">Tidak ada data
                                ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex justify-end text-center text-sm text-gray-600">
            <div>
                <p>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Kepala Sekolah</p>
                <br><br><br>
                <p class="font-bold underline">{{ $profil->kepala_sekolah ?? 'Nama Kepala Sekolah' }}</p>
                <p>NIP. {{ $profil->nip_kepala ?? '-' }}</p>
            </div>
        </div>
    </div>
@endsection
