<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FleetFlow ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>FleetFlow ERP</h1>
            <p style="text-align:center; color:var(--text-light); font-size:0.85rem; margin-top:-1rem; margin-bottom:1.25rem;">
                <a href="{{ route('publik.beranda') }}" style="color:var(--primary); text-decoration:none;">&larr; Kembali ke Halaman Publik</a>
            </p>
            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label>Username / Email</label>
                    <input type="text" name="username" id="usernameInput" class="form-input" required autofocus>
                    @error('username')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="passwordInput" class="form-input" required>
                </div>
                <button type="submit" class="btn">Masuk</button>
            </form>

            @if(config('app.env') !== 'production' || config('app.demo'))
            <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--border);">
                <div style="font-size:0.75rem; font-weight:700; color:var(--text-light); text-transform:uppercase; margin-bottom:0.5rem; text-align:center;">
                    Demo Quick Login (Portofolio)
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.4rem;">
                    <button type="button" class="btn btn-sm" style="background:#334155;" onclick="isiLogin('adminpusat')">Admin Pusat</button>
                    <button type="button" class="btn btn-sm" style="background:#0F766E;" onclick="isiLogin('keuangan')">Staf Keuangan</button>
                    <button type="button" class="btn btn-sm" style="background:#1D4ED8;" onclick="isiLogin('adminjkt')">Admin Cabang JKT</button>
                    <button type="button" class="btn btn-sm" style="background:#B45309;" onclick="isiLogin('sopir1')">Portal Sopir</button>
                </div>
            </div>
            <script>
                function isiLogin(user) {
                    document.getElementById('usernameInput').value = user;
                    document.getElementById('passwordInput').value = 'password';
                    document.getElementById('loginForm').submit();
                }
            </script>
            @endif
        </div>
    </div>
</body>
</html>
