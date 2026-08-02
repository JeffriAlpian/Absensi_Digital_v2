<nav style="flex: 1; overflow-y: auto; padding: 8px 0;">

    {{-- UTAMA --}}
    <div style="margin-bottom: 24px;">
        <p style="font-size: 11px; font-weight: 700; color: var(--gd-text-secondary); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 12px; margin-bottom: 8px;">UTAMA</p>

        <a href="{{ route('dashboard.index') }}"
            style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; text-decoration: none; transition: background 150ms ease, color 150ms ease; margin-bottom: 2px;
            {{ request()->routeIs('dashboard.index') ? 'background: var(--gd-surface-2); color: var(--gd-text-primary);' : 'color: var(--gd-text-secondary);' }}"
            onmouseover="if(!this.getAttribute('data-active')) { this.style.background='var(--gd-surface-2)'; this.style.color='var(--gd-text-primary)'; }"
            onmouseout="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--gd-text-secondary)'; }"
            {{ request()->routeIs('dashboard.index') ? "data-active=1" : "" }}>
            @if(request()->routeIs('dashboard.index'))
                <span style="width: 4px; height: 24px; background: var(--gd-primary); border-radius: 9999px; flex-shrink: 0; margin-left: -4px;"></span>
            @else
                <span style="width: 4px; flex-shrink: 0;"></span>
            @endif
            <i class="fas fa-home" style="width: 16px; text-align: center; font-size: 14px;"></i>
            <span>Dashboard</span>
        </a>
    </div>

    {{-- DATA MASTER --}}
    <div style="margin-bottom: 24px;">
        <p style="font-size: 11px; font-weight: 700; color: var(--gd-text-secondary); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 12px; margin-bottom: 8px;">DATA MASTER</p>

        @php
            $masterLinks = [
                ['route' => 'siswa.index',       'icon' => 'fas fa-user-graduate',         'label' => 'Data Siswa',       'badge' => $jumlah_siswa ?? null, 'match' => 'siswa.index'],
                ['route' => 'guru.index',        'icon' => 'fas fa-chalkboard-teacher',    'label' => 'Data Guru',        'badge' => $jumlah_guru ?? null,  'match' => 'guru.index'],
                ['route' => 'kartu.index',       'icon' => 'fas fa-address-card',          'label' => 'Data Kartu',       'badge' => null,                   'match' => 'kartu.index'],
                ['route' => 'kelas.index',       'icon' => 'fas fa-building',              'label' => 'Data Kelas',       'badge' => null,                   'match' => 'kelas.index'],
                ['route' => 'wakel.index',       'icon' => 'fas fa-person',               'label' => 'Data Wakel',       'badge' => null,                   'match' => 'wakel.index'],
                ['route' => 'device_rfid.index', 'icon' => 'fa-brands fa-nfc-directional', 'label' => 'Device RFID',      'badge' => null,                   'match' => 'device_rfid.index'],
                ['route' => 'hari-libur.index',  'icon' => 'fas fa-calendar-day',          'label' => 'Hari Libur',       'badge' => null,                   'match' => 'hari-libur.*'],
            ];
        @endphp

        @foreach($masterLinks as $link)
            @php $isActive = request()->routeIs($link['match']); @endphp
            <a href="{{ route($link['route']) }}"
                style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; text-decoration: none; transition: background 150ms ease, color 150ms ease; margin-bottom: 2px;
                {{ $isActive ? 'background: var(--gd-surface-2); color: var(--gd-text-primary);' : 'color: var(--gd-text-secondary);' }}"
                onmouseover="if(!this.getAttribute('data-active')) { this.style.background='var(--gd-surface-2)'; this.style.color='var(--gd-text-primary)'; }"
                onmouseout="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--gd-text-secondary)'; }"
                {{ $isActive ? "data-active=1" : "" }}>
                @if($isActive)
                    <span style="width: 4px; height: 24px; background: var(--gd-primary); border-radius: 9999px; flex-shrink: 0; margin-left: -4px;"></span>
                @else
                    <span style="width: 4px; flex-shrink: 0;"></span>
                @endif
                <i class="{{ $link['icon'] }}" style="width: 16px; text-align: center; font-size: 13px;"></i>
                <span style="flex: 1;">{{ $link['label'] }}</span>
                @if($link['badge'] !== null)
                    <span style="background: var(--gd-surface-2); color: var(--gd-primary); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; border: 1px solid var(--gd-border);">
                        {{ $link['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ABSENSI --}}
    <div style="margin-bottom: 24px;">
        <p style="font-size: 11px; font-weight: 700; color: var(--gd-text-secondary); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 12px; margin-bottom: 8px;">ABSENSI</p>

        @php
            $absensiLinks = [
                ['route' => 'absensi.scan.index',   'icon' => 'fas fa-qrcode',        'label' => 'Scan Masuk/Pulang',  'match' => 'absensi.scan.index'],
                ['route' => 'absensi.manual.index',  'icon' => 'fas fa-pen-to-square', 'label' => 'Input Manual / Izin','match' => 'absensi.manual.index'],
            ];
        @endphp

        @foreach($absensiLinks as $link)
            @php $isActive = request()->routeIs($link['match']); @endphp
            <a href="{{ route($link['route']) }}"
                style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; text-decoration: none; transition: background 150ms ease, color 150ms ease; margin-bottom: 2px;
                {{ $isActive ? 'background: var(--gd-surface-2); color: var(--gd-text-primary);' : 'color: var(--gd-text-secondary);' }}"
                onmouseover="if(!this.getAttribute('data-active')) { this.style.background='var(--gd-surface-2)'; this.style.color='var(--gd-text-primary)'; }"
                onmouseout="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--gd-text-secondary)'; }"
                {{ $isActive ? "data-active=1" : "" }}>
                @if($isActive)
                    <span style="width: 4px; height: 24px; background: var(--gd-primary); border-radius: 9999px; flex-shrink: 0; margin-left: -4px;"></span>
                @else
                    <span style="width: 4px; flex-shrink: 0;"></span>
                @endif
                <i class="{{ $link['icon'] }}" style="width: 16px; text-align: center; font-size: 13px;"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- LAPORAN --}}
    <div style="margin-bottom: 24px;">
        <p style="font-size: 11px; font-weight: 700; color: var(--gd-text-secondary); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 12px; margin-bottom: 8px;">LAPORAN</p>

        @php
            $laporanLinks = [
                ['route' => 'rekap.harian',   'icon' => 'fas fa-list-check',  'label' => 'Rekap Harian',  'match' => 'rekap.harian'],
                ['route' => 'rekap.bulanan',  'icon' => 'fas fa-file-lines',  'label' => 'Rekap Bulanan', 'match' => 'rekap.bulanan'],
            ];
        @endphp

        @foreach($laporanLinks as $link)
            @php $isActive = request()->routeIs($link['match']); @endphp
            <a href="{{ route($link['route']) }}"
                style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; text-decoration: none; transition: background 150ms ease, color 150ms ease; margin-bottom: 2px;
                {{ $isActive ? 'background: var(--gd-surface-2); color: var(--gd-text-primary);' : 'color: var(--gd-text-secondary);' }}"
                onmouseover="if(!this.getAttribute('data-active')) { this.style.background='var(--gd-surface-2)'; this.style.color='var(--gd-text-primary)'; }"
                onmouseout="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--gd-text-secondary)'; }"
                {{ $isActive ? "data-active=1" : "" }}>
                @if($isActive)
                    <span style="width: 4px; height: 24px; background: var(--gd-primary); border-radius: 9999px; flex-shrink: 0; margin-left: -4px;"></span>
                @else
                    <span style="width: 4px; flex-shrink: 0;"></span>
                @endif
                <i class="{{ $link['icon'] }}" style="width: 16px; text-align: center; font-size: 13px;"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- SISTEM --}}
    <div style="margin-bottom: 8px;">
        <p style="font-size: 11px; font-weight: 700; color: var(--gd-text-secondary); text-transform: uppercase; letter-spacing: 0.1em; padding: 0 12px; margin-bottom: 8px;">SISTEM</p>

        @php $isActive = request()->routeIs('pengaturan.index'); @endphp
        <a href="{{ route('pengaturan.index') }}"
            style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; font-size: 14px; font-weight: 700; text-decoration: none; transition: background 150ms ease, color 150ms ease; margin-bottom: 2px;
            {{ $isActive ? 'background: var(--gd-surface-2); color: var(--gd-text-primary);' : 'color: var(--gd-text-secondary);' }}"
            onmouseover="if(!this.getAttribute('data-active')) { this.style.background='var(--gd-surface-2)'; this.style.color='var(--gd-text-primary)'; }"
            onmouseout="if(!this.getAttribute('data-active')) { this.style.background='transparent'; this.style.color='var(--gd-text-secondary)'; }"
            {{ $isActive ? "data-active=1" : "" }}>
            @if($isActive)
                <span style="width: 4px; height: 24px; background: var(--gd-primary); border-radius: 9999px; flex-shrink: 0; margin-left: -4px;"></span>
            @else
                <span style="width: 4px; flex-shrink: 0;"></span>
            @endif
            <i class="fas fa-gear" style="width: 16px; text-align: center; font-size: 13px;"></i>
            <span>Pengaturan</span>
        </a>
    </div>

</nav>
