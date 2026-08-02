<nav class="fixed bottom-0 left-0 right-0 z-50">
    <div class="max-w-md mx-auto bg-white dark:bg-[#181818] dark:text-white border-t border-gray-200 dark:border-[#282828] shadow-[0_-5px_15px_rgba(0,0,0,0.05)] relative">

        <div class="flex justify-between items-end px-6 py-2">

            <a href="{{ route('dashboard.guru.index') }}"
                class="flex flex-col items-center gap-1 w-16 group {{ request()->routeIs('dashboard.guru.index') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500' }}">
                <i class="fa-solid fa-house text-xl mb-0.5 transition-transform group-active:scale-90"></i>
                <span class="text-[10px] font-medium">Home</span>
            </a>

            <a href="{{ route('dashboard.guru.riwayat.index') }}"
                class="flex flex-col items-center gap-1 w-16 group {{ request()->routeIs('dashboard.guru.riwayat.index') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500' }}">
                <i class="fa-solid fa-clock-rotate-left text-xl mb-0.5 transition-transform group-active:scale-90"></i>
                <span class="text-[10px] font-medium">Riwayat</span>
            </a>

            <div class="relative -top-6">
                <a href="{{ route('dashboard.guru.absen.index') }}"
                    class="flex flex-col items-center justify-center w-16 h-16 rounded-full shadow-lg shadow-indigo-300 transform transition-all active:scale-95 {{ request()->routeIs('scan.*') ? 'bg-indigo-700 ring-4 ring-indigo-100' : 'bg-indigo-600' }}">
                    <i class="fa-solid fa-fingerprint text-white text-2xl"></i>
                </a>
            </div>

            <a href="{{ route('dashboard.guru.profile.index') }}"
                class="flex flex-col items-center gap-1 w-16 group {{ request()->routeIs('dashboard.guru.profile.index') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-500' }}">
                <i class="fa-solid fa-user text-xl mb-0.5 transition-transform group-active:scale-90"></i>
                <span class="text-[10px] font-medium">Akun</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center gap-1 w-16 group">
                @csrf
                <button type="submit"
                    class="w-10 h-10 bg-red-50 rounded-full shadow-sm flex items-center justify-center text-red-500">
                    <i class="fa-solid fa-power-off "></i>
                </button>
                <span class="text-[10px] text-gray-500 dark:text-[#A7A7A7]">Logout</span>
            </form>

        </div>
    </div>
</nav>
