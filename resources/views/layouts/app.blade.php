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
        <aside class="sidebar">
            <div class="sidebar-header">FleetFlow</div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
                <a href="{{ route('pesanan.index') }}" class="nav-item">Pesanan & Resi</a>
                <a href="{{ route('pengiriman.index') }}" class="nav-item">Penjadwalan & Manifest</a>
                <a href="{{ route('kendaraan.index') }}" class="nav-item">Armada Kendaraan</a>
                <a href="{{ route('karyawan.index') }}" class="nav-item">Sopir & Karyawan</a>
                <a href="{{ route('bbm.index') }}" class="nav-item">Pencatatan BBM</a>
                <a href="{{ route('servis.index') }}" class="nav-item">Servis & Suku Cadang</a>
                @if(Auth::user()->hasRole('admin_pusat'))
                <a href="{{ route('cabang.index') }}" class="nav-item">Data Cabang</a>
                @endif
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div>
                    <strong>{{ Auth::user()->name }}</strong>
                    <span class="badge active" style="margin-left:0.5rem;">{{ strtoupper(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#DC2626; cursor:pointer; font-weight:bold;">Keluar</button>
                </form>
            </header>
            <div class="content-area">
                @if(session('success'))
                    <div style="background:#DCFCE7; color:#166534; padding:0.75rem 1rem; border-radius:0.5rem; margin-bottom:1rem;">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div style="background:#FEE2E2; color:#991B1B; padding:0.75rem 1rem; border-radius:0.5rem; margin-bottom:1rem;">
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
