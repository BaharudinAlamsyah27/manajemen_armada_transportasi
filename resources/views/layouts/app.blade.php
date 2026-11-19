<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleetFlow ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="brand">
                    <span class="brand-badge">FF</span>
                    <span>FleetFlow ERP</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <div class="nav-group-title">Utama</div>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
                        Dashboard Operasional
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Operasional</div>
                    <a href="{{ route('pesanan.index') }}" class="nav-item {{ request()->routeIs('pesanan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Pesanan & Resi
                    </a>
                    <a href="{{ route('pengiriman.index') }}" class="nav-item {{ request()->routeIs('pengiriman.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                        Penjadwalan & Manifest
                    </a>
                    <a href="{{ route('peta.index') }}" class="nav-item {{ request()->routeIs('peta.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Peta Armada (GPS)
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Armada & SDM</div>
                    <a href="{{ route('kendaraan.index') }}" class="nav-item {{ request()->routeIs('kendaraan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7h8m-8 4h8m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                        Armada Kendaraan
                    </a>
                    <a href="{{ route('karyawan.index') }}" class="nav-item {{ request()->routeIs('karyawan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Sopir & Karyawan
                    </a>
                    <a href="{{ route('absensi.index') }}" class="nav-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Absensi Harian
                    </a>
                    <a href="{{ route('bbm.index') }}" class="nav-item {{ request()->routeIs('bbm.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Pencatatan BBM
                    </a>
                    <a href="{{ route('servis.index') }}" class="nav-item {{ request()->routeIs('servis.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Servis & Suku Cadang
                    </a>
                    <a href="{{ route('verifikasi.index') }}" class="nav-item {{ request()->routeIs('verifikasi.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verifikasi Lapangan
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Penagihan & Keuangan</div>
                    <a href="{{ route('penagihan.index') }}" class="nav-item {{ request()->routeIs('penagihan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Penagihan & Invoice
                    </a>
                    @if(Auth::user()->hasAnyRole(['admin_pusat', 'keuangan']))
                    <a href="{{ route('keuangan.index') }}" class="nav-item {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Akuntansi & Keuangan
                    </a>
                    <a href="{{ route('aset.index') }}" class="nav-item {{ request()->routeIs('aset.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Aset, Hutang & Kasbon
                    </a>
                    <a href="{{ route('penggajian.index') }}" class="nav-item {{ request()->routeIs('penggajian.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Penggajian
                    </a>
                    @endif
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Laporan & Komunikasi</div>
                    <a href="{{ route('laporan.operasional') }}" class="nav-item {{ request()->routeIs('laporan.operasional') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Laporan Armada
                    </a>
                    <a href="{{ route('pengumuman.index') }}" class="nav-item {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Pengumuman
                    </a>
                    <a href="{{ route('laporan.riwayat') }}" class="nav-item {{ request()->routeIs('laporan.riwayat') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat Perubahan
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Pengaturan</div>
                    @if(Auth::user()->hasRole('admin_pusat'))
                    <a href="{{ route('cabang.index') }}" class="nav-item {{ request()->routeIs('cabang.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Data Cabang
                    </a>
                    @endif
                    <a href="{{ route('password.ganti') }}" class="nav-item {{ request()->routeIs('password.ganti') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Ganti Kata Sandi
                    </a>
                </div>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <strong>{{ Auth::user()->name }}</strong>
                    <span class="badge active">{{ strtoupper(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#DC2626; cursor:pointer; font-weight:600; font-size:0.88rem;">Keluar</button>
                </form>
            </header>
            <div class="content-area">
                @if(session('success'))
                    <div class="notif-sukses">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="notif-gagal">
                        <ul style="margin:0; padding-left:1.25rem;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
