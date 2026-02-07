<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Absensi V2</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Marquee Animation */
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-content {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 20s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">

    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white z-40 shrink-0">
        <div class="marquee-container py-2 px-4 relative">
            <div class="marquee-content text-sm font-semibold">
                🚀 Sistem Absensi Digital v2.0 • Guru tidak akan pernah tergantikan oleh robot • Jangan lupa absen tepat
                waktu!
            </div>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden relative">

        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden transition-opacity opacity-0">
        </div>

        <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col h-full shadow-xl lg:shadow-none">

            <div class="p-5 border-b border-gray-100 flex flex-col items-center justify-center shrink-0">
                @if (isset($sekolah_info) && $sekolah_info->logo_sekolah)
                    <img src="{{ asset('storage/' . $sekolah_info->logo_sekolah) }}" alt="Logo"
                        class="w-14 h-14 object-contain mb-2">
                @else
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center mb-2 shadow-md">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                @endif
                <h1 class="text-lg font-bold text-gray-800 text-center leading-tight">
                    {{ $sekolah_info->nama_sekolah ?? 'Absensi Sekolah' }}
                </h1>
            </div>

            <div class="p-4 border-b border-gray-100 flex items-center gap-3 shrink-0">
                <div class="relative">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-600"></i>
                    </div>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white">
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? 'email@sekolah.sch.id' }}</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
                <x-navbar />
            </nav>

            <div class="p-4 border-t border-gray-100 shrink-0">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 p-3 rounded-xl text-red-600 hover:bg-red-50 font-medium transition-colors">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50 relative">

            <header
                class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
                <div class="flex items-center gap-3">
                    <button id="menu-toggle"
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-800 truncate">@yield('title')</h1>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <div
                        class="hidden md:block bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-indigo-100">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </div>

                    <button class="p-2 rounded-lg hover:bg-gray-100 relative text-gray-600 transition-colors">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <div class="relative">
                        <button id="user-menu-btn"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                {{ substr(Auth::user()->username ?? 'U', 0, 2) }}
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                        </button>

                        <div id="user-dropdown"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50 transform origin-top-right transition-all">
                            <div class="px-4 py-3 border-b border-gray-100 md:hidden">
                                <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->username ?? 'User' }}</p>
                            </div>
                            <a href=""
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                                    class="fas fa-user mr-2 text-gray-400"></i> Profil Saya</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i
                                    class="fas fa-cog mr-2 text-gray-400"></i> Pengaturan</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i
                                        class="fas fa-sign-out-alt mr-2"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>

            <footer class="bg-white border-t border-gray-200 px-6 py-4 shrink-0">
                <div class="text-center md:text-left text-xs text-gray-500">
                    &copy; {{ date('Y') }} {{ $sekolah_info->nama_sekolah ?? 'Sekolah' }} <span
                        class="hidden sm:inline">• Sistem Absensi V2</span>
                </div>
            </footer>
        </div>
    </div>

    <a href="{{ url('/scan') }}"
        class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center text-white text-xl shadow-lg shadow-green-500/30 z-40 lg:hidden hover:scale-105 transition-transform">
        <i class="fas fa-qrcode"></i>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const menuToggle = document.getElementById('menu-toggle');

            // Toggle Sidebar Mobile
            function toggleSidebar() {
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    // Open
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                    setTimeout(() => backdrop.classList.remove('opacity-0'), 10); // Fade in
                } else {
                    // Close
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => backdrop.classList.add('hidden'), 300); // Wait fade out
                }
            }

            menuToggle.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', toggleSidebar);

            // User Dropdown
            const userBtn = document.getElementById('user-menu-btn');
            const userDropdown = document.getElementById('user-dropdown');

            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>
