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
                @if(Auth::user()->hasRole('admin_pusat'))
                <a href="{{ route('cabang.index') }}" class="nav-item">Cabang</a>
                @endif
                <a href="{{ route('karyawan.index') }}" class="nav-item">Karyawan</a>
                <a href="{{ route('kendaraan.index') }}" class="nav-item">Kendaraan</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div>Halo, {{ Auth::user()->name }}</div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#DC2626; cursor:pointer; font-weight:bold;">Keluar</button>
                </form>
            </header>
            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>