<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4F46E5">
    <title>Portal Sopir — FleetFlow</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="portal-body">
    <header class="portal-top">
        <div>
            <strong>FleetFlow Sopir</strong><br>
            <small>{{ Auth::user()->name }}</small>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </header>

    <main class="portal-main">
        @if(session('success'))
            <div class="notif-sukses">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="notif-gagal">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    @if(!Auth::user()->wajib_ganti_password)
    <nav class="portal-nav">
        <a href="{{ route('portal.beranda') }}" class="{{ request()->routeIs('portal.beranda') ? 'aktif' : '' }}">Beranda</a>
        <a href="{{ route('portal.jadwal') }}" class="{{ request()->routeIs('portal.jadwal', 'portal.pengiriman') ? 'aktif' : '' }}">Jadwal</a>
        <a href="{{ route('portal.pengumuman') }}" class="{{ request()->routeIs('portal.pengumuman') ? 'aktif' : '' }}">Pengumuman</a>
        <a href="{{ route('portal.slip') }}" class="{{ request()->routeIs('portal.slip') ? 'aktif' : '' }}">Slip Gaji</a>
        <a href="{{ route('portal.profil') }}" class="{{ request()->routeIs('portal.profil', 'password.ganti') ? 'aktif' : '' }}">Profil</a>
    </nav>
    @endif

    <script>
        // Cegah kirim ganda: nonaktifkan tombol submit setelah formulir dikirim.
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                form.querySelectorAll('button[type="submit"]').forEach(function (b) {
                    b.disabled = true;
                    b.textContent = 'Memproses...';
                });
            });
        });
    </script>
</body>
</html>
