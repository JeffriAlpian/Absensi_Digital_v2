@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    /* =============================================
       DASHBOARD – GREEN DECK STYLES
    ============================================= */

    /* Stat Cards */
    .gd-stat-card {
        background: var(--gd-surface);
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 200ms ease, transform 200ms ease;
        cursor: default;
    }
    .gd-stat-card:hover {
        background: var(--gd-surface-2);
        transform: translateY(-2px);
    }
    .gd-stat-icon {
        width: 48px; height: 48px;
        border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .gd-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--gd-text-secondary);
        margin-bottom: 6px;
    }
    .gd-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--gd-text-primary);
        line-height: 1;
    }

    /* Chart Cards */
    .gd-chart-card {
        background: var(--gd-surface);
        border-radius: 8px;
        padding: 20px;
    }
    .gd-chart-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--gd-text-primary);
        margin: 0 0 16px 0;
    }

    /* Section title */
    .gd-section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--gd-text-primary);
        margin: 0;
    }
    .gd-section-subtitle {
        font-size: 13px;
        color: var(--gd-text-secondary);
        margin: 4px 0 0 0;
    }

    /* WA Monitor Cards */
    .gd-monitor-card {
        background: var(--gd-surface);
        border-radius: 8px;
        padding: 20px;
        transition: background 200ms ease;
    }
    .gd-monitor-card:hover { background: var(--gd-surface-2); }
    .gd-monitor-icon {
        width: 52px; height: 52px;
        border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .gd-monitor-value {
        font-size: 36px;
        font-weight: 800;
        color: var(--gd-text-primary);
        line-height: 1;
    }
    .gd-monitor-label {
        font-size: 13px;
        color: var(--gd-text-secondary);
        margin: 4px 0 0 0;
    }

    /* Info Tip */
    .gd-tip {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        background: rgba(245, 155, 35, 0.1);
        border: 1px solid rgba(245, 155, 35, 0.25);
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 12px;
        color: var(--gd-warning);
        margin-top: 12px;
    }

    /* Buttons */
    .gd-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 9px 20px;
        background: var(--gd-primary);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.05em;
        border: none;
        border-radius: 9999px;
        cursor: pointer;
        transition: background 150ms ease, transform 150ms ease;
        font-family: 'DM Sans', sans-serif;
        text-decoration: none;
        white-space: nowrap;
    }
    .gd-btn-primary:hover {
        background: var(--gd-primary-hover);
        transform: scale(1.04);
    }

    .gd-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 18px;
        background: transparent;
        color: var(--gd-text-primary);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.05em;
        border: 1px solid var(--gd-secondary);
        border-radius: 9999px;
        cursor: pointer;
        transition: border-color 150ms ease, background 150ms ease;
        font-family: 'DM Sans', sans-serif;
        text-decoration: none;
        white-space: nowrap;
    }
    .gd-btn-secondary:hover {
        border-color: var(--gd-text-primary);
        background: rgba(255,255,255,0.05);
    }

    .gd-btn-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 18px;
        background: transparent;
        color: var(--gd-error);
        font-size: 13px;
        font-weight: 700;
        border: 1px solid var(--gd-error);
        border-radius: 9999px;
        cursor: pointer;
        transition: background 150ms ease;
        font-family: 'DM Sans', sans-serif;
    }
    .gd-btn-danger:hover { background: rgba(226,33,52,0.1); }

    /* Table */
    .gd-table-card {
        background: var(--gd-surface);
        border-radius: 8px;
        overflow: hidden;
    }
    .gd-table-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gd-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .gd-table-header-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--gd-text-primary);
        margin: 0;
    }
    .gd-table-link {
        font-size: 13px;
        font-weight: 700;
        color: var(--gd-primary);
        text-decoration: none;
        transition: color 150ms ease;
    }
    .gd-table-link:hover { color: var(--gd-primary-hover); }

    table.gd-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    table.gd-table thead tr {
        background: var(--gd-surface-2);
    }
    table.gd-table thead th {
        padding: 10px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gd-text-secondary);
        white-space: nowrap;
    }
    table.gd-table tbody tr {
        border-bottom: 1px solid var(--gd-border);
        transition: background 150ms ease;
    }
    table.gd-table tbody tr:last-child { border-bottom: none; }
    table.gd-table tbody tr:hover { background: var(--gd-surface-2); }
    table.gd-table td {
        padding: 12px 16px;
        color: var(--gd-text-secondary);
    }
    table.gd-table td.gd-td-primary {
        color: var(--gd-text-primary);
        font-weight: 600;
    }
    table.gd-table td.gd-td-mono {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
    }

    /* Badges */
    .gd-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
    }
    .gd-badge-green  { background: rgba(29,185,84,0.15);  color: var(--gd-primary); }
    .gd-badge-yellow { background: rgba(245,155,35,0.15); color: var(--gd-warning); }
    .gd-badge-red    { background: rgba(226,33,52,0.15);  color: var(--gd-error); }
    .gd-badge-blue   { background: rgba(59,130,246,0.15); color: #3B82F6; }
    .gd-badge-gray   { background: var(--gd-surface-2);   color: var(--gd-text-secondary); }

    /* Legend dots */
    .gd-legend-dot {
        width: 10px; height: 10px;
        border-radius: 9999px;
        flex-shrink: 0;
    }

    /* Refresh button */
    .gd-btn-refresh {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        background: var(--gd-surface-2);
        color: var(--gd-text-primary);
        font-size: 13px;
        font-weight: 700;
        border: 1px solid var(--gd-border);
        border-radius: 9999px;
        cursor: pointer;
        transition: background 150ms ease, transform 150ms ease;
        font-family: 'DM Sans', sans-serif;
    }
    .gd-btn-refresh:hover {
        background: var(--gd-surface-3);
        transform: scale(1.03);
    }

    /* Empty state */
    .gd-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
        text-align: center;
        color: var(--gd-text-secondary);
    }
    .gd-empty i {
        font-size: 40px;
        margin-bottom: 12px;
        color: var(--gd-surface-2);
    }
    .gd-empty p { margin: 4px 0; }
</style>

<div style="display: flex; flex-direction: column; gap: 24px;">

    {{-- ===== STAT CARDS ===== --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">

        {{-- Total Siswa --}}
        <div class="gd-stat-card">
            <div>
                <p class="gd-stat-label">Total Siswa</p>
                <p class="gd-stat-value">{{ $stats['total_siswa'] }}</p>
            </div>
            <div class="gd-stat-icon" style="background: rgba(59,130,246,0.15); color: #60A5FA;">
                <i class="fas fa-users"></i>
            </div>
        </div>

        {{-- Total Guru --}}
        <div class="gd-stat-card">
            <div>
                <p class="gd-stat-label">Total Guru</p>
                <p class="gd-stat-value">{{ $stats['total_guru'] }}</p>
            </div>
            <div class="gd-stat-icon" style="background: rgba(168,85,247,0.15); color: #C084FC;">
                <i class="fas fa-chalkboard-user"></i>
            </div>
        </div>

        {{-- Hadir Hari Ini --}}
        <div class="gd-stat-card">
            <div>
                <p class="gd-stat-label">Hadir Hari Ini</p>
                <p class="gd-stat-value" style="color: var(--gd-primary);">{{ $stats['hadir_hari_ini'] }}</p>
            </div>
            <div class="gd-stat-icon" style="background: rgba(29,185,84,0.15); color: var(--gd-primary);">
                <i class="fas fa-check"></i>
            </div>
        </div>

        {{-- Alpha / Belum --}}
        <div class="gd-stat-card">
            <div>
                <p class="gd-stat-label">Alpha / Belum</p>
                <p class="gd-stat-value" style="color: var(--gd-error);">{{ $stats['alpha_hari_ini'] }}</p>
            </div>
            <div class="gd-stat-icon" style="background: rgba(226,33,52,0.15); color: var(--gd-error);">
                <i class="fas fa-user-xmark"></i>
            </div>
        </div>
    </div>

    {{-- ===== CHARTS ===== --}}
    <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;" class="chart-grid">

            {{-- Trend Chart --}}
            <div class="gd-chart-card">
                <h3 class="gd-chart-title">Tren Kehadiran (7 Hari Terakhir)</h3>
                <div style="position: relative; height: 260px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>

            {{-- Doughnut Chart --}}
            <div class="gd-chart-card">
                <h3 class="gd-chart-title">Komposisi Hari Ini</h3>
                <div style="position: relative; height: 180px; display: flex; justify-content: center;">
                    <canvas id="todayCompositionChart"></canvas>
                </div>
                <div style="margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gd-text-secondary);">
                        <span class="gd-legend-dot" style="background: #1DB954;"></span> Hadir ({{ $pieData['H'] }})
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gd-text-secondary);">
                        <span class="gd-legend-dot" style="background: #F59B23;"></span> Sakit ({{ $pieData['S'] }})
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gd-text-secondary);">
                        <span class="gd-legend-dot" style="background: #60A5FA;"></span> Izin ({{ $pieData['I'] }})
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--gd-text-secondary);">
                        <span class="gd-legend-dot" style="background: #E22134;"></span> Alpha ({{ $pieData['A'] }})
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 900px) {
            .chart-grid { grid-template-columns: 1fr !important; }
        }
    </style>

    {{-- ===== WHATSAPP SERVER MONITOR ===== --}}
    <div style="border-top: 1px solid var(--gd-border); padding-top: 24px;">

        <div style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px;">
            <div>
                <h2 class="gd-section-title">
                    <i class="fas fa-satellite-dish" style="color: var(--gd-primary); margin-right: 8px; font-size: 18px;"></i>
                    WhatsApp Server Monitor
                </h2>
                <p class="gd-section-subtitle">Pantau status pengiriman pesan real-time</p>
            </div>
            <button onclick="window.location.reload()" class="gd-btn-refresh">
                <i class="fas fa-rotate-right"></i>
                Refresh Data
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">

            {{-- Pending --}}
            <div class="gd-monitor-card" style="border-left: 3px solid var(--gd-warning);">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                    <div class="gd-monitor-icon" style="background: rgba(245,155,35,0.15);">
                        <i class="fas fa-clock" style="color: var(--gd-warning); font-size: 22px;"></i>
                    </div>
                    <div>
                        <p class="gd-monitor-value">{{ $pendingCount }}</p>
                        <p class="gd-monitor-label">Antrean (Pending)</p>
                    </div>
                </div>
                <div class="gd-tip">
                    <i class="fas fa-lightbulb" style="flex-shrink:0; margin-top:1px;"></i>
                    <span>Menunggu giliran dikirim oleh Worker. Jika angka ini menumpuk, cek apakah worker berjalan.</span>
                </div>
            </div>

            {{-- Failed --}}
            <div class="gd-monitor-card" style="border-left: 3px solid {{ $failedCount > 0 ? 'var(--gd-error)' : 'var(--gd-primary)' }};">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div class="gd-monitor-icon" style="background: {{ $failedCount > 0 ? 'rgba(226,33,52,0.15)' : 'rgba(29,185,84,0.15)' }};">
                            @if($failedCount > 0)
                                <i class="fas fa-triangle-exclamation" style="color: var(--gd-error); font-size: 22px;"></i>
                            @else
                                <i class="fas fa-circle-check" style="color: var(--gd-primary); font-size: 22px;"></i>
                            @endif
                        </div>
                        <div>
                            <p class="gd-monitor-value" style="{{ $failedCount > 0 ? 'color: var(--gd-error);' : '' }}">{{ $failedCount }}</p>
                            <p class="gd-monitor-label">Gagal Kirim (Failed)</p>
                        </div>
                    </div>

                    @if($failedCount > 0)
                        <div style="display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                            <form action="{{ route('wa.retry') }}" method="POST">
                                @csrf
                                <button type="submit" class="gd-btn-primary" style="font-size: 12px; padding: 7px 14px;">
                                    <i class="fas fa-rotate-right"></i> Retry All
                                </button>
                            </form>
                            <form action="{{ route('wa.flush') }}" method="POST"
                                onsubmit="return confirm('Yakin hapus semua log error? Data tidak bisa kembali.')">
                                @csrf
                                <button type="submit" class="gd-btn-danger" style="font-size: 12px; padding: 6px 14px; width: 100%;">
                                    <i class="fas fa-trash"></i> Hapus Log
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tabel Antrean --}}
        <div class="gd-table-card">
            <div class="gd-table-header">
                <h3 class="gd-table-header-title">Daftar Antrean Terakhir</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="gd-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>ID Job</th>
                            <th>Waktu</th>
                            <th>Percobaan</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingJobs as $job)
                            <tr>
                                <td><span class="gd-badge gd-badge-yellow">⏳ Pending</span></td>
                                <td class="gd-td-mono">#{{ $job->id }}</td>
                                <td>{{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}</td>
                                <td>{{ $job->attempts }}x</td>
                                <td style="color: var(--gd-text-secondary); font-style: italic;">Menunggu worker...</td>
                            </tr>
                        @endforeach

                        @foreach ($failedJobs as $job)
                            <tr style="background: rgba(226,33,52,0.04);">
                                <td><span class="gd-badge gd-badge-red">❌ Failed</span></td>
                                <td class="gd-td-mono">#{{ $job->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                                <td>—</td>
                                <td style="color: var(--gd-error); max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                    title="{{ $job->exception }}">
                                    {{ Str::limit($job->exception, 50) }}
                                </td>
                            </tr>
                        @endforeach

                        @if($pendingJobs->isEmpty() && $failedJobs->isEmpty())
                            <tr>
                                <td colspan="5">
                                    <div class="gd-empty">
                                        <i class="fas fa-circle-check" style="color: var(--gd-primary) !important;"></i>
                                        <p style="font-size: 15px; font-weight: 700; color: var(--gd-text-primary);">Semua Bersih!</p>
                                        <p style="font-size: 13px;">Tidak ada antrean tertunda atau pesan gagal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== AKTIVITAS ABSENSI TERBARU ===== --}}
    <div class="gd-table-card">
        <div class="gd-table-header">
            <h3 class="gd-table-header-title">
                <i class="fas fa-wave-square" style="color: var(--gd-primary); margin-right: 8px;"></i>
                Aktivitas Absensi Terbaru
            </h3>
            <a href="{{ route('rekap.harian') }}" class="gd-table-link">Lihat Semua →</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="gd-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestActivities as $log)
                        <tr>
                            <td class="gd-td-mono">
                                {{ \Carbon\Carbon::parse($log->updated_at)->format('H:i') }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--gd-text-primary);">
                                    {{ $log->siswa->nama ?? ($log->guru->nama ?? 'Unknown') }}
                                </span>
                                <span style="display: block; font-size: 11px; color: var(--gd-text-secondary); margin-top: 2px;">
                                    {{ $log->siswa ? 'Siswa' : 'Guru' }}
                                </span>
                            </td>
                            <td>
                                @if($log->status == 'H')
                                    <span class="gd-badge gd-badge-green">Hadir</span>
                                @elseif($log->status == 'S')
                                    <span class="gd-badge gd-badge-yellow">Sakit</span>
                                @elseif($log->status == 'I')
                                    <span class="gd-badge gd-badge-blue">Izin</span>
                                @else
                                    <span class="gd-badge gd-badge-red">{{ $log->status }}</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: var(--gd-text-secondary);">
                                {{ $log->keterangan ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="gd-empty">
                                    <i class="fas fa-inbox"></i>
                                    <p style="font-size: 14px; font-weight: 700; color: var(--gd-text-primary);">Belum ada aktivitas</p>
                                    <p style="font-size: 13px;">Belum ada aktivitas absensi hari ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ========================
    // Chart.js Global Defaults (Green Deck)
    // ========================
    Chart.defaults.color = 'var(--gd-text-secondary)';
    Chart.defaults.font.family = "'DM Sans', sans-serif";

    // ========================
    // 1. LINE CHART – Tren Kehadiran
    // ========================
    const ctxTrend = document.getElementById('attendanceTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Hadir',
                    data: {!! json_encode($chartDataHadir) !!},
                    borderColor: '#1DB954',
                    backgroundColor: 'rgba(29, 185, 84, 0.08)',
                    pointBackgroundColor: '#1DB954',
                    pointBorderColor: '#1DB954',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                },
                {
                    label: 'Terlambat',
                    data: {!! json_encode($chartDataTelat) !!},
                    borderColor: '#F59B23',
                    backgroundColor: 'rgba(245, 155, 35, 0.06)',
                    pointBackgroundColor: '#F59B23',
                    pointBorderColor: '#F59B23',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    hidden: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'var(--gd-text-secondary)',
                        font: { size: 12, weight: '600', family: "'DM Sans', sans-serif" },
                        boxWidth: 12,
                        boxHeight: 12,
                        borderRadius: 9999,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16,
                    }
                },
                tooltip: {
                    backgroundColor: 'var(--gd-surface-2)',
                    titleColor: 'var(--gd-text-primary)',
                    bodyColor: 'var(--gd-text-secondary)',
                    borderColor: 'var(--gd-border)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    grid: { color: 'var(--gd-border)', drawBorder: false },
                    ticks: { color: 'var(--gd-text-secondary)', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'var(--gd-border)', drawBorder: false },
                    ticks: { color: 'var(--gd-text-secondary)', font: { size: 11 } }
                }
            }
        }
    });

    // ========================
    // 2. DOUGHNUT CHART – Komposisi Hari Ini
    // ========================
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
                backgroundColor: ['#1DB954', '#F59B23', '#60A5FA', '#E22134'],
                hoverBackgroundColor: ['#1ED760', '#FBBF24', '#93C5FD', '#F87171'],
                borderWidth: 0,
                spacing: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'var(--gd-surface-2)',
                    titleColor: 'var(--gd-text-primary)',
                    bodyColor: 'var(--gd-text-secondary)',
                    borderColor: 'var(--gd-border)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                }
            }
        }
    });
</script>

@endsection
