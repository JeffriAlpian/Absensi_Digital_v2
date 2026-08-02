<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Absensi V2</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Initialize Theme before rendering to avoid flash -->
    <script>
        const storedTheme = localStorage.getItem('gd-theme');
        if (storedTheme === 'light' || (!storedTheme && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.setAttribute('data-theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Green Deck Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* =============================================
           GREEN DECK – DESIGN SYSTEM TOKENS
        ============================================= */
        :root[data-theme="dark"] {
            --gd-primary:        #1DB954;
            --gd-primary-hover:  #1ED760;
            --gd-secondary:      #535353;
            --gd-neutral:        #B3B3B3;
            --gd-bg:             #121212;
            --gd-surface:        #181818;
            --gd-surface-2:      #282828;
            --gd-surface-3:      #333333;
            --gd-text-primary:   #FFFFFF;
            --gd-text-secondary: #A7A7A7;
            --gd-border:         #282828;
            --gd-warning:        #F59B23;
            --gd-error:          #E22134;
        }

        :root[data-theme="light"] {
            --gd-primary:        #1DB954;
            --gd-primary-hover:  #1ED760;
            --gd-secondary:      #E5E7EB; /* gray-200 */
            --gd-neutral:        #6B7280; /* gray-500 */
            --gd-bg:             #F3F4F6; /* gray-100 */
            --gd-surface:        #FFFFFF; /* white */
            --gd-surface-2:      #F9FAFB; /* gray-50 */
            --gd-surface-3:      #E5E7EB; /* gray-200 */
            --gd-text-primary:   #111827; /* gray-900 */
            --gd-text-secondary: #4B5563; /* gray-600 */
            --gd-border:         #E5E7EB; /* gray-200 */
            --gd-warning:        #F59B23;
            --gd-error:          #E22134;
        }


        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--gd-bg);
            color: var(--gd-text-primary);
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ---- Scrollbar ---- */
        .gd-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .gd-scrollbar::-webkit-scrollbar-track {
            background: var(--gd-bg);
        }
        .gd-scrollbar::-webkit-scrollbar-thumb {
            background: var(--gd-surface-2);
            border-radius: 10px;
        }
        .gd-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--gd-secondary);
        }

        /* ---- Marquee ---- */
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
        }
        .marquee-content {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 22s linear infinite;
        }
        @keyframes marquee {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        /* ---- Sidebar ---- */
        #sidebar {
            background-color: var(--gd-surface);
            border-right: 1px solid var(--gd-border);
            width: 240px;
        }

        /* ---- Top Header ---- */
        #topbar {
            background-color: var(--gd-surface);
            border-bottom: 1px solid var(--gd-border);
        }

        /* ---- Main Area ---- */
        #main-content {
            background-color: var(--gd-bg);
        }

        /* ---- Footer ---- */
        #footer {
            background-color: var(--gd-surface);
            border-top: 1px solid var(--gd-border);
        }

        /* ---- User avatar initials ---- */
        .gd-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--gd-primary), #17a349);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        /* ---- Dropdown ---- */
        .gd-dropdown {
            background-color: var(--gd-surface-2);
            border: 1px solid var(--gd-border);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        }
        .gd-dropdown a,
        .gd-dropdown button {
            color: var(--gd-text-primary);
            font-size: 14px;
            transition: background 150ms ease;
        }
        .gd-dropdown a:hover,
        .gd-dropdown button:hover {
            background-color: var(--gd-surface-3);
        }

        /* ---- Notification dot ---- */
        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--gd-error);
            border-radius: 9999px;
            border: 2px solid var(--gd-surface);
        }

        /* ---- Date badge ---- */
        .gd-date-badge {
            background: var(--gd-surface-2);
            color: var(--gd-primary);
            border: 1px solid var(--gd-border);
            border-radius: 9999px;
            padding: 4px 14px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        /* ---- FAB (Floating QR) ---- */
        .gd-fab {
            background: var(--gd-primary);
            color: #fff;
            border-radius: 9999px;
            width: 56px; height: 56px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: 0 8px 24px rgba(29,185,84,0.4);
            transition: transform 200ms ease, background 200ms ease;
        }
        .gd-fab:hover {
            background: var(--gd-primary-hover);
            transform: scale(1.06);
        }

        /* ---- Logo placeholder ---- */
        .gd-logo-placeholder {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--gd-primary), #17a349);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 8px;
            box-shadow: 0 4px 16px rgba(29,185,84,0.3);
        }

        /* ---- Sidebar user area ---- */
        .gd-sidebar-user {
            background: var(--gd-surface);
            border-bottom: 1px solid var(--gd-border);
            padding: 12px 16px;
        }
        .gd-sidebar-user-avatar {
            width: 36px; height: 36px;
            background: var(--gd-surface-2);
            border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .gd-online-dot {
            position: absolute; bottom: 0; right: 0;
            width: 10px; height: 10px;
            background: var(--gd-primary);
            border-radius: 9999px;
            border: 2px solid #000;
        }

        /* ---- Mobile sidebar backdrop ---- */
        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 30;
        }

        /* ---- Sidebar toggle (mobile) ---- */
        #sidebar {
            position: fixed;
            inset-y: 0;
            left: 0;
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 300ms ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        @media (min-width: 1024px) {
            #sidebar {
                position: static;
                transform: none !important;
            }
            #sidebar-backdrop {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    {{-- ===== MARQUEE BANNER ===== --}}
    <div style="background: var(--gd-primary); flex-shrink: 0; z-index: 40;">
        <div class="marquee-container" style="padding: 7px 16px;">
            <div class="marquee-content" style="font-size: 13px; font-weight: 600; color: #fff; letter-spacing: 0.02em;">
                🚀 Sistem Absensi Digital v2.0 &nbsp;•&nbsp; Guru tidak akan pernah tergantikan oleh robot &nbsp;•&nbsp; Jangan lupa absen tepat waktu!
            </div>
        </div>
    </div>

    <div style="display: flex; flex: 1; overflow: hidden; position: relative;">

        {{-- ===== SIDEBAR BACKDROP (MOBILE) ===== --}}
        <div id="sidebar-backdrop" class="hidden" style="opacity: 0; transition: opacity 300ms ease;"></div>

        {{-- ===== SIDEBAR ===== --}}
        <aside id="sidebar" class="gd-scrollbar">

            {{-- Logo --}}
            <div style="padding: 20px 16px; border-bottom: 1px solid var(--gd-border); display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
                @if(isset($sekolah_info) && $sekolah_info->logo_sekolah)
                    <img src="{{ asset('storage/' . $sekolah_info->logo_sekolah) }}" alt="Logo"
                        style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 8px;">
                @else
                    <div class="gd-logo-placeholder">
                        <i class="fas fa-building" style="color: #fff; font-size: 20px;"></i>
                    </div>
                @endif
                <h1 style="font-size: 14px; font-weight: 700; color: var(--gd-text-primary); text-align: center; line-height: 1.3; margin: 0;">
                    {{ $sekolah_info->nama_sekolah ?? 'Absensi Sekolah' }}
                </h1>
            </div>

            {{-- User Info --}}
            <div class="gd-sidebar-user" style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                <div class="gd-sidebar-user-avatar">
                    <i class="fas fa-user" style="color: var(--gd-text-secondary); font-size: 14px;"></i>
                    <span class="gd-online-dot"></span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p style="font-weight: 600; color: var(--gd-text-primary); font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;">
                        {{ Auth::user()->name ?? 'Admin' }}
                    </p>
                    <p style="font-size: 11px; color: var(--gd-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;">
                        {{ Auth::user()->email ?? 'email@sekolah.sch.id' }}
                    </p>
                </div>
            </div>

            {{-- Nav --}}
            <nav style="flex: 1; overflow-y: auto; padding: 12px;" class="gd-scrollbar">
                <x-navbar />
            </nav>

            {{-- Logout --}}
            <div style="padding: 12px; border-top: 1px solid var(--gd-border); flex-shrink: 0;">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="display: flex; align-items: center; gap: 12px; width: 100%; padding: 10px 12px; border-radius: 8px; font-size: 14px; font-weight: 700; color: var(--gd-error); background: transparent; border: none; cursor: pointer; transition: background 150ms ease; font-family: 'DM Sans', sans-serif;"
                        onmouseover="this.style.background='rgba(226,33,52,0.1)'"
                        onmouseout="this.style.background='transparent'">
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- ===== MAIN COLUMN ===== --}}
        <div style="flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; background: var(--gd-bg);">

            {{-- Top Header --}}
            <header id="topbar" style="padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; position: sticky; top: 0; z-index: 20;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button id="menu-toggle" style="display: none; padding: 8px; border-radius: 8px; background: transparent; border: none; color: var(--gd-text-secondary); cursor: pointer; font-size: 18px; transition: background 150ms ease;"
                        onmouseover="this.style.background='var(--gd-surface-2)'"
                        onmouseout="this.style.background='transparent'">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 style="font-size: 20px; font-weight: 700; color: var(--gd-text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        @yield('title')
                    </h1>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="gd-date-badge" style="display: none;" id="date-badge">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </div>

                    <button id="theme-toggle-btn" style="position: relative; padding: 8px; border-radius: 8px; background: transparent; border: none; color: var(--gd-text-secondary); cursor: pointer; font-size: 15px; transition: background 150ms ease;"
                        onmouseover="this.style.background='var(--gd-surface-2)'"
                        onmouseout="this.style.background='transparent'">
                        <i id="theme-toggle-icon" class="fas fa-moon"></i>
                    </button>

                    <button style="position: relative; padding: 8px; border-radius: 8px; background: transparent; border: none; color: var(--gd-text-secondary); cursor: pointer; font-size: 15px; transition: background 150ms ease;"
                        onmouseover="this.style.background='var(--gd-surface-2)'"
                        onmouseout="this.style.background='transparent'">
                        <i class="fas fa-bell"></i>
                        <span class="notif-dot"></span>
                    </button>

                    <div style="position: relative;">
                        <button id="user-menu-btn" style="display: flex; align-items: center; gap: 8px; padding: 6px; border-radius: 8px; background: transparent; border: none; cursor: pointer; transition: background 150ms ease;"
                            onmouseover="this.style.background='var(--gd-surface-2)'"
                            onmouseout="this.style.background='transparent'">
                            <div class="gd-avatar">{{ substr(Auth::user()->username ?? 'U', 0, 2) }}</div>
                            <i class="fas fa-chevron-down" style="color: var(--gd-text-secondary); font-size: 11px;"></i>
                        </button>

                        <div id="user-dropdown" class="gd-dropdown" style="display: none; position: absolute; right: 0; margin-top: 8px; width: 180px; padding: 6px 0; z-index: 50;">
                            <div style="padding: 10px 14px; border-bottom: 1px solid var(--gd-border);">
                                <p style="font-size: 13px; font-weight: 600; color: var(--gd-text-primary); margin: 0;">{{ Auth::user()->username ?? 'User' }}</p>
                            </div>
                            <a href="{{ route('profile.index') }}" style="display: flex; align-items: center; gap: 8px; padding: 9px 14px; text-decoration: none; border-radius: 0;">
                                <i class="fas fa-user" style="color: var(--gd-text-secondary); font-size: 13px;"></i>
                                Profil Saya
                            </a>
                            <div style="border-top: 1px solid var(--gd-border); margin: 4px 0;"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" style="display: flex; align-items: center; gap: 8px; width: 100%; padding: 9px 14px; background: transparent; border: none; color: var(--gd-error); font-size: 14px; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 150ms ease;"
                                    onmouseover="this.style.background='rgba(226,33,52,0.1)'"
                                    onmouseout="this.style.background='transparent'">
                                    <i class="fas fa-sign-out-alt" style="font-size: 13px;"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main id="main-content" style="flex: 1; overflow-y: auto; padding: 24px 32px;" class="gd-scrollbar">
                <div style="max-width: 1400px; margin: 0 auto;">
                    @yield('content')
                </div>
            </main>

            {{-- Footer --}}
            <footer id="footer" style="padding: 14px 24px; flex-shrink: 0;">
                <div style="font-size: 12px; color: var(--gd-text-secondary); text-align: center;">
                    &copy; {{ date('Y') }} {{ $sekolah_info->nama_sekolah ?? 'Sekolah' }}
                    <span> &bull; Sistem Absensi V2</span>
                </div>
            </footer>
        </div>
    </div>

    {{-- FAB QR (Mobile) --}}
    <a href="{{ url('/scan') }}" class="gd-fab" style="position: fixed; bottom: 24px; right: 24px; z-index: 40; text-decoration: none;">
        <i class="fas fa-qrcode"></i>
    </a>

    <style>
        @media (max-width: 1023px) {
            #menu-toggle { display: block !important; }
            #date-badge { display: none !important; }
        }
        @media (min-width: 768px) {
            #date-badge { display: block !important; }
        }
        @media (min-width: 1024px) {
            .gd-fab { display: none !important; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const menuToggle = document.getElementById('menu-toggle');

            function toggleSidebar() {
                const isClosed = sidebar.classList.contains('gd-sidebar-closed') ||
                    sidebar.style.transform === 'translateX(-100%)' ||
                    !sidebar.getAttribute('data-open');

                if (!sidebar.getAttribute('data-open')) {
                    sidebar.style.transform = 'translateX(0)';
                    sidebar.setAttribute('data-open', '1');
                    backdrop.classList.remove('hidden');
                    setTimeout(() => backdrop.style.opacity = '1', 10);
                } else {
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebar.removeAttribute('data-open');
                    backdrop.style.opacity = '0';
                    setTimeout(() => backdrop.classList.add('hidden'), 300);
                }
            }

            if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
            if (backdrop)   backdrop.addEventListener('click', toggleSidebar);

            // User Dropdown
            const userBtn      = document.getElementById('user-menu-btn');
            const userDropdown = document.getElementById('user-dropdown');

            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = userDropdown.style.display === 'none' || !userDropdown.style.display;
                userDropdown.style.display = isHidden ? 'block' : 'none';
            });

            document.addEventListener('click', (e) => {
                if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.style.display = 'none';
                }
            });

            // Theme Toggle Logic
            const themeBtn = document.getElementById('theme-toggle-btn');
            const themeIcon = document.getElementById('theme-toggle-icon');

            function updateThemeIcon(theme) {
                if (theme === 'dark') {
                    themeIcon.className = 'fas fa-sun'; // Show sun in dark mode to switch to light
                } else {
                    themeIcon.className = 'fas fa-moon'; // Show moon in light mode to switch to dark
                }
            }

            // Init icon
            updateThemeIcon(document.documentElement.getAttribute('data-theme'));

            themeBtn.addEventListener('click', () => {
                let currentTheme = document.documentElement.getAttribute('data-theme');
                let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('gd-theme', newTheme);
                updateThemeIcon(newTheme);
            });
        });
    </script>
</body>

</html>