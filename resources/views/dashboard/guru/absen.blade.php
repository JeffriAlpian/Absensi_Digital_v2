@extends('layouts.guru')

@section('title', 'Absen Masuk')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="pt-8 px-6">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">{{ $data_guru->nama }}</h1>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
        </div>

        <div class="bg-white p-3 rounded-2xl shadow-lg border border-gray-100 mb-6 relative">
            <div id="status-lokasi"
                class="absolute top-5 left-1/2 transform -translate-x-1/2 z-[400] bg-white/90 backdrop-blur px-4 py-2 rounded-full shadow-md text-xs font-bold text-gray-600 flex items-center gap-2">
                <i class="fa-solid fa-spinner fa-spin text-indigo-600"></i> Mencari Lokasi...
            </div>

            <div id="map" class="h-64 w-full rounded-xl z-0"></div>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboard.guru.absen.store') }}" method="POST" id="form-absen">
            @csrf

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div class="bg-indigo-50 rounded-xl p-4 mb-6 text-center border border-indigo-100">
                <p class="text-xs text-gray-500 mb-1">Koordinat Terdeteksi</p>
                <p class="font-mono text-sm font-bold text-indigo-700" id="info-koordinat">- , -</p>
            </div>

            <button type="submit" id="btn-absen" disabled
                class="w-full bg-gray-300 text-gray-500 font-bold py-4 rounded-xl shadow-sm transition-all flex justify-center items-center gap-2 cursor-not-allowed">
                <i class="fa-solid fa-location-dot"></i>
                Lokasi Belum Ditemukan
            </button>
        </form>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Inisialisasi Peta (Default view Indonesia)
            var map = L.map('map').setView([-6.200000, 106.816666], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker;

            // 2. Fungsi Sukses (Lokasi Ditemukan)
            function success(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                var accuracy = position.coords.accuracy;

                // Update Input Hidden Form
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Update Teks Visual
                document.getElementById('info-koordinat').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

                // Update Map View
                map.setView([lat, lng], 18); // Zoom in ke lokasi

                // Hapus marker lama jika ada, buat baru
                if (marker) map.removeLayer(marker);

                // Tambahkan Marker Custom
                marker = L.marker([lat, lng]).addTo(map)
                    .bindPopup("Posisi Anda saat ini (Akurasi: " + Math.round(accuracy) + "m)").openPopup();

                // Tambahkan Lingkaran Akurasi (Opsional)
                L.circle([lat, lng], {
                    radius: accuracy
                }).addTo(map);

                // Update Status UI
                var statusDiv = document.getElementById('status-lokasi');
                statusDiv.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i> Lokasi Terkunci';
                statusDiv.classList.remove('text-gray-600');
                statusDiv.classList.add('text-green-700', 'border', 'border-green-200');

                // Aktifkan Tombol Submit
                var btn = document.getElementById('btn-absen');
                btn.disabled = false;
                btn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                btn.classList.add('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'shadow-lg',
                    'shadow-indigo-200');
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Absen Sekarang';
            }

            // 3. Fungsi Error
            function error(err) {
                var statusDiv = document.getElementById('status-lokasi');
                statusDiv.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-red-500"></i> Gagal Deteksi';

                alert("Gagal mengambil lokasi: " + err.message + ". Pastikan GPS aktif dan izinkan akses lokasi.");
            }

            // 4. Jalankan Geolocation
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(success, error, {
                    enableHighAccuracy: true, // Paksa GPS presisi tinggi
                    timeout: 10000, // Tunggu maks 10 detik
                    maximumAge: 0 // Jangan pakai cache lokasi lama
                });
            } else {
                alert("Browser Anda tidak mendukung Geolocation.");
            }
        });
    </script>
@endsection
