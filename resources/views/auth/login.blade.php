<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Optional: Add a subtle loading spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border-left-color: #ffffff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body
    class="bg-gradient-to-r from-green-500 to-green-700 flex flex-col items-center justify-center min-h-screen font-sans px-4">

    <div
        class="bg-white dark:bg-[#181818] dark:text-white p-6 md:p-8 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-500 ease-in-out">
        <div class="text-center mb-4">
            @if ($profil)
                <img src="{{ asset('storage/' . ($profil->logo_sekolah ? $profil->logo_sekolah : 'logo')) }}"
                    alt="Logo Sekolah" class="mx-auto h-16 mb-2">
                <h1 class="text-xl font-bold text-green-700">{{ $profil->nama_sekolah ?? 'Sekolah Kita' }}</h1>
            @else
                <img src="{{ asset('storage/logo_sekolah/default-logo.png') }}" alt="Logo Sekolah"
                    class="mx-auto h-16 mb-2">
                <h1 class="text-xl font-bold text-green-700">Sekolah Kita</h1>
            @endif
        </div>

        <h2 class="text-2xl font-semibold text-center mb-5 text-gray-700 dark:text-gray-200">Login Absensi</h2>

        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-md text-sm">
            📌 <strong>Fokus Aplikasi:</strong> Mempermudah Administrasi Kesiswaan & mendukung Tupoksi Wali Kelas.
        </div>
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-3 mb-5 rounded-md text-sm">
            ℹ️ Orang tua/Wali Siswa dapat login memantau kehadiran. (Username: NISN, Password: NISN)
        </div>

        <form id="loginForm" method="post" action="app/cek.php">
            <div id="loginMessage" class="mb-4 text-sm"></div>
            <div class="mb-4">
                <label for="username" class="sr-only">Username</label>
                <input type="text" id="username" name="username" placeholder="Username" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150 ease-in-out">
            </div>
            <div class="mb-5">
                <label for="password" class="sr-only">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-[#333333] rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-150 ease-in-out">
            </div>
            <button type="submit" id="loginButton"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-150 ease-in-out flex items-center justify-center">
                <span id="buttonText">Masuk</span>
                <div id="loadingSpinner" class="spinner ml-2 hidden"></div>
            </button>
        </form>
    </div>

    <div class="mt-6 text-center text-sm text-white">
        <a href="tentang.html" class="font-semibold hover:text-yellow-300 mx-2">Tentang</a> |
        <a href="https://www.instagram.com/jeffrikw" target="_blank"
            class="font-semibold hover:text-yellow-300 mx-2">Kontak</a> |
        <a href="https://jeffri.rf.gd/" class="font-semibold hover:text-yellow-300 mx-2">Unduh</a>
        <div class="mt-1 text-xs opacity-80">Versi Aplikasi 1.00</div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginMessage = document.getElementById('loginMessage');
        const loginButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('buttonText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        loginForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Stop default form submission

            // Show loading state
            loginMessage.innerHTML = ''; // Clear previous messages
            loginMessage.className = 'mb-4 text-sm'; // Reset class
            buttonText.textContent = 'Memproses...';
            loadingSpinner.classList.remove('hidden');
            loginButton.disabled = true;

            const formData = new FormData(loginForm);

            fetch("{{ route('login') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json' // Penting agar Laravel membalas JSON
                    },
                    body: formData
                })
                .then(response => {
                    // Kita baca JSON-nya dulu, apapun status codenya (mau 200, 401, atau 422)
                    return response.json().then(data => {
                        // Gabungkan status HTTP dengan data JSON agar bisa diproses di .then berikutnya
                        return {
                            ok: response.ok, // true jika 200-299
                            status: response.status, // 200, 401, 422, dll
                            data: data // Isi pesan dari controller
                        };
                    });
                })
                .then(res => {
                    // === SKENARIO 1: LOGIN SUKSES (HTTP 200) ===
                    if (res.ok) {
                        loginMessage.innerHTML =
                            `<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative">
                <i class="fas fa-check-circle mr-2"></i>${res.data.message}
            </div>`;

                        // Redirect
                        setTimeout(() => {
                            window.location.href = res.data.redirectUrl;
                        }, 1000);
                    }
                    // === SKENARIO 2: LOGIN GAGAL / SALAH PASSWORD (HTTP 401 atau 422) ===
                    else {
                        // Ambil pesan error dari JSON controller
                        let errorMessage = res.data.message || 'Login Gagal';

                        // Jika ada error validasi detail (misal: username kosong)
                        if (res.data.errors) {
                            errorMessage = Object.values(res.data.errors).flat().join('<br>');
                        }

                        loginMessage.innerHTML =
                            `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative">
                <i class="fas fa-exclamation-circle mr-2"></i>${errorMessage}
            </div>`;

                        resetButton();
                    }
                })
                .catch(error => {
                    // === SKENARIO 3: ERROR KONEKSI / SERVER MATI (Network Error) ===
                    console.error('Fetch error:', error);
                    loginMessage.innerHTML =
                        `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative">
            <i class="fas fa-wifi mr-2"></i>Gagal terhubung ke server. Cek koneksi internet.
        </div>`;
                    resetButton();
                });
        });

        function resetButton() {
            buttonText.textContent = 'Masuk';
            loadingSpinner.classList.add('hidden');
            loginButton.disabled = false;
        }
    </script>
</body>

</html>
