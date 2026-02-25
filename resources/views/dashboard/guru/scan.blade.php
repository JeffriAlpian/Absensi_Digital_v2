@extends('layouts.guru')

@section('title', 'Scan Absensi')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">

        <div class="max-w-md mx-auto">

            <h2 class="mb-6 text-2xl font-bold text-gray-800 tracking-tight">
                <i class="fa-solid fa-qrcode mr-2 text-green-600"></i>Absensi
            </h2>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative mb-6">
                <div class="bg-green-600 px-4 py-3 flex items-center justify-between">
                    <span class="text-white font-medium text-sm flex items-center gap-2">
                        <i class="fa-solid fa-camera"></i> Kamera Belakang
                    </span>
                    <div id="scan-status-badge"
                        class="bg-green-500 text-white text-xs px-2 py-1 rounded-full bg-opacity-50 border border-green-400">
                        Ready
                    </div>
                </div>

                <div class="relative bg-black group h-80">
                    <div id="reader" class="w-full h-full object-cover"></div>

                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="scan-laser absolute w-62 h-0.5 bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)] z-20">
                        </div>

                        <div class="w-62 h-62 border-2 border-white/50 rounded-lg relative z-10 box-content">
                            <div
                                class="absolute top-0 left-0 w-12 h-12 border-t-4 border-l-4 border-green-500 -mt-0.5 -ml-0.5 ">
                            </div>
                            <div
                                class="absolute top-0 right-0 w-12 h-12 border-t-4 border-r-4 border-green-500 -mt-0.5 -mr-0.5 ">
                            </div>
                            <div
                                class="absolute bottom-0 left-0 w-12 h-12 border-b-4 border-l-4 border-green-500 -mb-0.5 -ml-0.5 ">
                            </div>
                            <div
                                class="absolute bottom-0 right-0 w-12 h-12 border-b-4 border-r-4 border-green-500 -mb-0.5 -mr-0.5 ">
                            </div>
                        </div>

                        <div class="absolute inset-0 bg-black/30"></div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 text-center border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        Arahkan kamera ke QR Code Kartu Pelajar
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider ml-1">Aktivitas Terkini</h3>

                <div id="result-container" class="space-y-3 min-h-25">
                    <div id="empty-state" class="text-center py-8 bg-white rounded-xl border border-dashed border-gray-300">
                        <div class="text-gray-300 mb-2">
                            <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
                        </div>
                        <p class="text-sm text-gray-400">Belum ada scan masuk hari ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <audio id="beepSound" src="{{ asset('audio/beep.mp3') }}" preload="auto"></audio>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        /* CSS Khusus untuk Animasi Laser Scanner */
        @keyframes scanAnimation {
            0% {
                transform: translateY(-120px);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(120px);
                opacity: 0;
            }
        }

        .scan-laser {
            animation: scanAnimation 2s infinite linear;
        }

        /* Override style library agar video full cover */
        #reader video {
            object-fit: cover !important;
            height: 100% !important;
            border-radius: 0 !important;
        }

        /* Sembunyikan elemen bawaan library yang tidak perlu */
        #reader__scan_region {
            display: none !important;
        }

        #reader__dashboard_section_csr span {
            display: none !important;
        }

        #qr-shaded-region div {
            display: none !important;
        }
    </style>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let lastScannedCode = null;
        let scanCooldown = false;

        // Variabel untuk menyimpan lokasi
        let currentLocation = {
            latitude: null,
            longitude: null
        };

        // --- 1. AMBIL LOKASI SAAT HALAMAN DIMUAT ---
        document.addEventListener("DOMContentLoaded", function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        currentLocation.latitude = position.coords.latitude;
                        currentLocation.longitude = position.coords.longitude;
                        console.log("📍 Lokasi ditemukan:", currentLocation);

                        // Opsional: Update badge status visual jika perlu
                        // document.getElementById('scan-status-badge').innerHTML = 'Ready + GPS';
                    },
                    function(error) {
                        console.warn("⚠️ Gagal mengambil lokasi:", error.message);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 5000
                    }
                );
            } else {
                console.error("Browser tidak mendukung Geolocation.");
            }
        });

        // --- 2. FUNGSI UI (TIDAK BERUBAH BANYAK) ---
        function updateStatus(status) {
            const badge = document.getElementById('scan-status-badge');
            if (status === 'scanning') {
                badge.className =
                    "bg-yellow-500 text-white text-xs px-3 py-1 rounded-full flex items-center gap-1 animate-pulse";
                badge.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...`;
            } else {
                badge.className = "bg-green-500 text-white text-xs px-3 py-1 rounded-full flex items-center gap-1";
                badge.innerHTML = `<i class="fa-solid fa-check"></i> Siap`;
            }
        }

        function addResultCard(message, type = 'success') {
            const container = document.getElementById("result-container");
            const emptyState = document.getElementById("empty-state");
            if (emptyState) emptyState.style.display = 'none';

            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const isSuccess = type === 'success';
            const borderColor = isSuccess ? 'border-l-green-500' : 'border-l-red-500';
            const iconColor = isSuccess ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50';
            const iconClass = isSuccess ? 'fa-circle-check' : 'fa-circle-xmark';

            // Sanitasi pesan agar tag <br> tetap jalan tapi script tidak
            // (Sederhana: kita percaya output controller aman karena kita yang buat)

            const cardHTML = `
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 border-l-4 ${borderColor} flex items-start gap-4 transition-all duration-500 animate-fade-in-down">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full ${iconColor} flex items-center justify-center">
                        <i class="fa-solid ${iconClass} text-xl"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-gray-800 break-words leading-snug">
                        ${message} 
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                        <i class="fa-regular fa-clock"></i> ${time}
                    </p>
                </div>
            </div>
        `;

            const wrapper = document.createElement('div');
            wrapper.innerHTML = cardHTML;
            container.prepend(wrapper.firstElementChild);

            if (container.children.length > 5) {
                container.lastElementChild.remove();
            }
        }

        // --- 3. LOGIKA SCANNER ---
        function onScanSuccess(qrMessage) {
            if (scanCooldown || qrMessage === lastScannedCode) return;

            lastScannedCode = qrMessage;
            scanCooldown = true;
            updateStatus('scanning');

            // Play Beep
            const beep = document.getElementById("beepSound");
            if (beep) beep.play().catch(() => {});

            // --- FETCH REQUEST ---
            fetch("{{ route('scan.storeScan') }}", { // Pastikan nama route benar
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken // Header Token Wajib Laravel
                    },
                    body: JSON.stringify({
                        qrcode: qrMessage,
                        latitude: currentLocation.latitude, // Kirim Lat
                        longitude: currentLocation.longitude // Kirim Long
                    })
                })
                .then(response => {
                    // Cek jika response bukan OK (misal 404/500), tetap parse JSON errornya
                    return response.json().then(data => ({
                        status: response.status,
                        body: data
                    }));
                })
                .then(({
                    status,
                    body
                }) => {
                    updateStatus('ready');

                    // Logika Status: Jika success dari JSON body ATAU status code 200
                    const isSuccess = (body.status === 'success');
                    const type = isSuccess ? 'success' : 'error';

                    addResultCard(body.message, type);

                    // Cooldown
                    setTimeout(() => {
                        scanCooldown = false;
                        lastScannedCode = null; // Reset agar bisa scan kode yang sama lagi
                    }, 2500);
                })
                .catch(error => {
                    console.error(error);
                    updateStatus('ready');
                    addResultCard("Gagal koneksi ke server.", 'error');
                    setTimeout(() => {
                        scanCooldown = false;
                    }, 2000);
                });
        }

        // --- 4. INISIALISASI KAMERA ---
        const html5QrCode = new Html5Qrcode("reader");
        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            aspectRatio: 1.0
        };

        html5QrCode.start({
                facingMode: "environment"
            },
            config,
            onScanSuccess
        ).catch(err => {
            document.getElementById("reader").innerHTML = `
            <div class="h-full flex flex-col items-center justify-center text-white p-6 text-center">
                <i class="fa-solid fa-camera-slash text-4xl mb-3 text-red-500"></i>
                <p class="font-bold">Kamera Gagal</p>
                <p class="text-xs text-gray-400">Pastikan izin kamera aktif & HTTPS digunakan.</p>
            </div>
        `;
        });
    </script>
@endsection
