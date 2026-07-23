<nav class="flex-1 overflow-y-auto sidebar-scrollbar p-4">
    <!-- Dashboard -->
    <div class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">UTAMA</p>
        <a href="{{ route('dashboard.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl font-medium mb-2 {{ request()->routeIs('dashboard.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }}">
            <i class="fas fa-home w-5 text-center"></i>
            <span>Dashboard</span>
        </a>
    </div>

    <!-- Data Master -->
    <div class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">DATA MASTER</p>
        <a href="{{ route('siswa.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('siswa.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2 ">
            <i class="fas fa-user-graduate w-5 text-center"></i>
            <span>Data Siswa</span>
            <span
                class="ml-auto bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $jumlah_siswa }}
            </span>
        </a>
        <a href="{{ route('guru.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('guru.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
            <span>Data Guru</span>
            <span
                class="ml-auto bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $jumlah_guru }}
            </span>
        </a>
        <a href="{{ route('kartu.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('kartu.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-address-card w-5 text-center"></i>
            <span>Data Kartu</span>
        </a>
        <a href="{{ route('kelas.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('kelas.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-building w-5 text-center"></i>
            <span>Data Kelas</span>
        </a>
        <a href="{{ route('wakel.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('wakel.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-person w-5 text-center"></i>
            <span>Data Wakel</span>
        </a>
        <a href="{{ route('device_rfid.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('device_rfid.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fa-brands fa-nfc-directional w-5 text-center"></i>
            <span>Data Device RFID</span>
        </a>
        <a href="{{ route('hari-libur.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('hari-libur.*') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-calendar-day w-5 text-center"></i>
            <span>Data Hari Libur</span>
        </a>
    </div>

    <!-- Absensi -->
    <div class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">ABSENSI</p>
        <a href="{{ route('absensi.scan.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('absensi.scan.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-qrcode w-5 text-center"></i>
            <span>Scan Masuk/Pulang</span>
        </a>
        <a href="{{ route('absensi.manual.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('absensi.manual.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-pen-to-square w-5 text-center"></i>
            <span>Input Manual / Izin</span>
        </a>

    </div>

    <!-- Laporan -->
    <div class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">LAPORAN</p>
        <a href="{{ route('rekap.harian') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('rekap.harian') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-list-check w-5 text-center"></i>
            <span>Rekap Harian</span>
        </a>
        <a href="{{ route('rekap.bulanan') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('rekap.bulanan') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-file-lines w-5 text-center"></i>
            <span>Rekap Bulanan</span>
        </a>
    </div>

    <!-- Sistem -->
    <div class="mb-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">SISTEM</p>
        <a href="{{ route('pengaturan.index') }}"
            class="flex items-center space-x-3 p-3 rounded-xl {{ request()->routeIs('pengaturan.index') ? 'bg-green-50 text-green-700 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600' }} font-medium mb-2">
            <i class="fas fa-gear w-5 text-center"></i>
            <span>Pengaturan</span>
        </a>
    </div>
</nav>
