<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleetFlow Logistics — Solusi Pengiriman Kargo & Manajemen Armada Terpadu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        html { scroll-behavior: smooth; }
        .pub-nav { background:rgba(255,255,255,0.95); backdrop-filter:blur(8px); border-bottom:1px solid var(--border); padding:0.9rem 2rem; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:50; }
        .pub-links { display:flex; gap:1.5rem; align-items:center; flex-wrap:wrap; }
        .pub-links a.nav-link { color:var(--text); text-decoration:none; font-weight:500; font-size:0.9rem; }
        .pub-links a.nav-link:hover { color:var(--primary); }
        .pub-hero { background:linear-gradient(135deg, #0F172A 0%, #1E3A8A 55%, #2563EB 100%); color:#fff; padding:4rem 1.5rem 5.5rem; }
        .pub-hero-inner { max-width:1140px; margin:0 auto; display:grid; grid-template-columns:1.15fr 0.85fr; gap:2.5rem; align-items:center; }
        .pub-hero h1 { font-size:2.45rem; font-weight:800; line-height:1.2; margin-bottom:1rem; }
        .pub-hero p { color:#CBD5E1; font-size:1.02rem; margin-bottom:1.75rem; line-height:1.65; }
        .pub-stats { display:flex; gap:2.25rem; flex-wrap:wrap; border-top:1px solid rgba(255,255,255,0.15); padding-top:1.25rem; margin-top:1.5rem; }
        .pub-container { max-width:1140px; margin:0 auto; padding:3rem 1.5rem; }
        .pub-grid-2 { display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem; margin-top:-4.25rem; position:relative; z-index:10; }
        .pub-box { background:#fff; border-radius:1rem; padding:1.75rem; border:1px solid var(--border); box-shadow:0 12px 30px rgba(15,23,42,0.08); }
        .pub-box h2 { font-size:1.15rem; margin-bottom:0.35rem; color:var(--text); }
        .pub-box p.sub { color:var(--text-light); font-size:0.85rem; margin-bottom:1.1rem; }
        .sec-title { text-align:center; max-width:680px; margin:0 auto 2.5rem; }
        .sec-title span { display:inline-block; background:var(--primary-soft); color:var(--primary); font-weight:700; font-size:0.75rem; padding:0.25rem 0.75rem; border-radius:999px; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem; }
        .sec-title h2 { font-size:1.75rem; font-weight:800; color:var(--text); margin-bottom:0.5rem; }
        .sec-title p { color:var(--text-light); font-size:0.95rem; }
        .feature-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.25rem; }
        .feature-card { background:#fff; border:1px solid var(--border); border-radius:0.85rem; padding:1.5rem; transition:transform 0.15s, box-shadow 0.15s; }
        .feature-card:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(15,23,42,0.06); }
        .feature-icon { width:42px; height:42px; border-radius:0.6rem; background:var(--primary-soft); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:700; margin-bottom:0.9rem; }
        .feature-card h3 { font-size:1.02rem; font-weight:700; margin-bottom:0.4rem; color:var(--text); }
        .feature-card p { color:var(--text-light); font-size:0.88rem; line-height:1.55; }
        .fleet-card { background:#fff; border:1px solid var(--border); border-radius:0.85rem; padding:1.35rem; text-align:center; }
        .fleet-badge { display:inline-block; background:#F1F5F9; color:#334155; font-size:0.75rem; font-weight:700; padding:0.2rem 0.65rem; border-radius:999px; margin-top:0.5rem; }
        .pub-footer { background:#0F172A; color:#94A3B8; padding:3.5rem 1.5rem 2rem; margin-top:3rem; }
        .pub-footer-inner { max-width:1140px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:2rem; }
        .pub-footer h4 { color:#fff; font-size:1rem; margin-bottom:0.85rem; }
        @media (max-width: 820px) { .pub-hero-inner { grid-template-columns:1fr; text-align:center; } .pub-stats { justify-content:center; } }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header class="pub-nav">
        <a href="{{ route('publik.beranda') }}" class="brand">
            <span class="brand-badge">FF</span>
            <span>FleetFlow Logistics</span>
        </a>
        <div class="pub-links">
            <a href="#tentang" class="nav-link">Tentang Kami</a>
            <a href="#keunggulan" class="nav-link">Keunggulan</a>
            <a href="#armada" class="nav-link">Armada</a>
            <a href="#cek-resi" class="nav-link">Lacak & Ongkir</a>
            <a href="#cabang" class="nav-link">Cabang & Kontak</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-sm">Dashboard ERP &rarr;</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm">Login Staff / Sopir</a>
            @endauth
        </div>
    </header>

    <!-- Hero Section -->
    <section class="pub-hero">
        <div class="pub-hero-inner">
            <div>
                <div style="display:inline-block; background:rgba(255,255,255,0.12); padding:0.3rem 0.85rem; border-radius:999px; font-size:0.8rem; font-weight:600; margin-bottom:1rem;">
                    🚚 Mitra Logistik & Distribusi Darat Terpercaya
                </div>
                <h1>Pengiriman Kargo Antar Kota Lebih Cepat, Aman & Terlacak</h1>
                <p>
                    <strong>PT FleetFlow Logistik Nusantara</strong> menghadirkan layanan ekspedisi kargo darat berjadwal dengan sistem manajemen armada digital, pelacakan GPS *real-time*, dan bukti serah-terima terverifikasi untuk mendukung rantai pasok bisnis Anda.
                </p>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <a href="#cek-resi" class="btn btn-sm" style="background:#fff; color:#1E3A8A; padding:0.7rem 1.25rem;">Lacak Kiriman Sekarang</a>
                    <a href="#cek-ongkir" class="btn btn-sm" style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3); padding:0.7rem 1.25rem;">Hitung Tarif Ongkir</a>
                </div>
                <div class="pub-stats">
                    <div><strong style="font-size:1.65rem; color:#fff;">{{ $ringkasan['total_cabang'] }}</strong><br><small>Cabang Operasional</small></div>
                    <div><strong style="font-size:1.65rem; color:#fff;">{{ $ringkasan['total_armada'] }} Unit</strong><br><small>Armada Siaga</small></div>
                    <div><strong style="font-size:1.65rem; color:#fff;">{{ $ringkasan['pesanan_terkirim'] }}+</strong><br><small>Pengiriman Sukses</small></div>
                    <div><strong style="font-size:1.65rem; color:#fff;">99%</strong><br><small>Ketepatan Jadwal</small></div>
                </div>
            </div>
            <div>
                {{-- Anda dapat mengganti public/images/hero-truck.svg dengan foto truk asli (mis. hero-truck.png) --}}
                <img src="{{ asset('images/hero-truck.svg') }}" alt="Armada Truk FleetFlow Logistics" style="width:100%; max-width:520px; display:block; margin:0 auto; border-radius:1rem; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
            </div>
        </div>
    </section>

    <div class="pub-container">
        <!-- Widget Lacak Resi & Kalkulator Ongkir -->
        <div class="pub-grid-2">
            <div class="pub-box" id="cek-resi">
                <h2>🔍 Lacak Status Resi Real-Time</h2>
                <p class="sub">Masukkan kode resi pengiriman Anda untuk melihat posisi dan riwayat perjalanan paket.</p>
                <form action="{{ route('publik.beranda') }}#cek-resi" method="GET" style="display:flex; gap:0.5rem; margin-bottom:1rem;">
                    <input type="text" name="resi" class="form-input" placeholder="Contoh: FF261118XXXXXX" value="{{ $kodeResi }}" required>
                    <button type="submit" class="btn btn-sm">Lacak</button>
                </form>

                @if($kodeResi !== '')
                    @if(!$hasilResi)
                        <div class="notif-gagal">Resi <strong>{{ strtoupper($kodeResi) }}</strong> tidak ditemukan. Pastikan nomor resi benar.</div>
                    @else
                        <div style="background:#F8FAFC; padding:1rem; border-radius:0.65rem; border:1px solid var(--border);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <strong>{{ $hasilResi->kode_resi }}</strong>
                                <span class="badge active">{{ strtoupper(str_replace('_', ' ', $hasilResi->status)) }}</span>
                            </div>
                            <div style="font-size:0.85rem; color:var(--text-light); margin-bottom:0.75rem;">
                                Rute: <strong>{{ $hasilResi->kota_asal ?? '-' }} &rarr; {{ $hasilResi->kota_tujuan }}</strong>
                            </div>
                            <div style="border-top:1px solid var(--border); padding-top:0.5rem;">
                                @foreach($hasilResi->statusLogs->take(5) as $log)
                                    <div style="font-size:0.82rem; margin-bottom:0.35rem;">
                                        <span style="color:var(--text-light);">{{ $log->waktu?->format('d/m/Y H:i') }}</span> —
                                        <strong>{{ strtoupper(str_replace('_', ' ', $log->status_baru)) }}</strong>
                                        @if($log->catatan) ({{ $log->catatan }}) @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="pub-box" id="cek-ongkir">
                <h2>📦 Kalkulator Estimasi Ongkir</h2>
                <p class="sub">Dapatkan perkiraan biaya pengiriman kargo secara transparan sebelum mengirim barang.</p>
                <form action="{{ route('publik.beranda') }}#cek-ongkir" method="GET">
                    <div style="display:grid; grid-template-columns:1fr 1fr 100px; gap:0.5rem; margin-bottom:0.75rem;">
                        <input type="text" name="asal" class="form-input" placeholder="Kota Asal (Jakarta)" value="{{ request('asal', 'Jakarta') }}" required>
                        <input type="text" name="tujuan" class="form-input" placeholder="Kota Tujuan (Bandung)" value="{{ request('tujuan', 'Bandung') }}" required>
                        <input type="number" step="0.5" min="1" name="berat" class="form-input" placeholder="Kg" value="{{ request('berat', 20) }}" required>
                    </div>
                    <button type="submit" class="btn btn-sm" style="width:100%;">Hitung Estimasi Biaya</button>
                </form>

                @if($hasilOngkir)
                    <div style="margin-top:1rem; background:#EFF6FF; border:1px solid #BFDBFE; padding:1rem; border-radius:0.65rem;">
                        <div style="font-size:0.85rem; color:#1E40AF;">
                            Rute: <strong>{{ $hasilOngkir['asal'] }} &rarr; {{ $hasilOngkir['tujuan'] }}</strong> ({{ $hasilOngkir['berat'] }} kg)
                        </div>
                        <div style="font-size:1.5rem; font-weight:800; color:#1D4ED8; margin:0.2rem 0;">
                            Rp {{ number_format($hasilOngkir['total'], 0, ',', '.') }}
                        </div>
                        <small style="color:#3B82F6;">Tarif Rp {{ number_format($hasilOngkir['harga_per_kg'], 0, ',', '.') }}/kg (Minimum Rp {{ number_format($hasilOngkir['minimum'], 0, ',', '.') }})</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tentang Perusahaan & Manfaat -->
        <section id="tentang" style="margin-top:4rem;">
            <div class="sec-title">
                <span>Tentang FleetFlow</span>
                <h2>Solusi Logistik Modern untuk Pertumbuhan Bisnis Anda</h2>
                <p>Kami memadukan armada darat yang andal dengan sistem ERP terintegrasi sehingga setiap pengiriman terpantau dari gudang asal hingga ke tangan penerima.</p>
            </div>
            <div class="feature-grid" id="keunggulan">
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>Pelacakan GPS & Status Real-Time</h3>
                    <p>Setiap armada dilengkapi pelacakan lokasi aktif dan pembaruan status otomatis mulai dari penjadwalan manifest, keberangkatan, hingga barang tiba di tujuan.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📸</div>
                    <h3>Bukti Serah-Terima Digital (e-POD)</h3>
                    <p>Sopir wajib mengunggah foto bukti barang diterima beserta nama penerima aktual langsung dari aplikasi lapangan, menjamin akuntabilitas 100%.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Standar Kelaikan & Keamanan Ketat</h3>
                    <p>Sistem kami memvalidasi otomatis masa berlaku STNK, KIR, Pajak, kesesuaian SIM sopir, serta batas kapasitas muatan (anti-overload) sebelum armada diberangkatkan.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Tarif Transparan & Penagihan Korporat</h3>
                    <p>Mendukung pengiriman ritel maupun kontrak perusahaan (B2B) dengan fasilitas termin pembayaran (TOP), faktur resmi, dan rekonsiliasi tagihan yang rapi.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔧</div>
                    <h3>Perawatan Armada Berkala</h3>
                    <p>Seluruh unit kendaraan menjalani jadwal servis rutin dan pemantauan efisiensi BBM untuk mencegah kendala teknis di perjalanan.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3>Jaringan Multi-Cabang Terhubung</h3>
                    <p>Koordinasi antar cabang di kota-kota utama Pulau Jawa memastikan jadwal keberangkatan multi-rit setiap hari berjalan tepat waktu.</p>
                </div>
            </div>
        </section>

        <!-- Jenis Armada -->
        <section id="armada" style="margin-top:4rem;">
            <div class="sec-title">
                <span>Kapasitas Armada</span>
                <h2>Pilihan Armada Sesuai Kebutuhan Muatan</h2>
                <p>Mulai dari pengiriman dokumen dan paket ekspres dalam kota hingga distribusi kargo tonase besar antar provinsi.</p>
            </div>
            <div class="grid-cards">
                <div class="fleet-card">
                    <div style="font-size:2.2rem; margin-bottom:0.5rem;">🏍️</div>
                    <h3>Kurir Motor</h3>
                    <p style="color:var(--text-light); font-size:0.85rem; margin:0.35rem 0;">Pengiriman cepat dokumen & paket kecil dalam kota.</p>
                    <span class="fleet-badge">Kapasitas s/d 50 Kg</span>
                </div>
                <div class="fleet-card">
                    <div style="font-size:2.2rem; margin-bottom:0.5rem;">🛻</div>
                    <h3>Pickup / Blindvan</h3>
                    <p style="color:var(--text-light); font-size:0.85rem; margin:0.35rem 0;">Distribusi ritel & pengiriman lincah area perkotaan.</p>
                    <span class="fleet-badge">Kapasitas s/d 1.000 Kg</span>
                </div>
                <div class="fleet-card">
                    <div style="font-size:2.2rem; margin-bottom:0.5rem;">🚚</div>
                    <h3>Truk Box (CDE / CDD)</h3>
                    <p style="color:var(--text-light); font-size:0.85rem; margin:0.35rem 0;">Kargo tertutup aman dari cuaca untuk pengiriman antar kota.</p>
                    <span class="fleet-badge">Kapasitas 2.000 – 4.000 Kg</span>
                </div>
                <div class="fleet-card">
                    <div style="font-size:2.2rem; margin-bottom:0.5rem;">🚛</div>
                    <h3>Truk Fuso / Heavy Cargo</h3>
                    <p style="color:var(--text-light); font-size:0.85rem; margin:0.35rem 0;">Angkutan logistik industri, gudang, dan distribusi partai besar.</p>
                    <span class="fleet-badge">Kapasitas 5.000 – 10.000 Kg</span>
                </div>
            </div>
        </section>

        <!-- Jaringan Cabang & Alamat -->
        <section id="cabang" style="margin-top:4rem;">
            <div class="sec-title">
                <span>Lokasi & Kontak</span>
                <h2>Jaringan Cabang & Alamat Operasional</h2>
                <p>Kunjungi cabang terdekat kami untuk penyerahan barang atau konsultasi kebutuhan logistik perusahaan Anda.</p>
            </div>
            <div class="grid-cards">
                @foreach($cabangs as $c)
                <div class="stat-card">
                    <h3>Cabang {{ $c->kode }} — {{ $c->kota }}</h3>
                    <div class="value" style="font-size:1.15rem; margin-bottom:0.4rem;">{{ $c->nama }}</div>
                    <div style="font-size:0.86rem; color:var(--text); margin-bottom:0.35rem;">
                        📍 {{ $c->alamat ?? 'Jl. Raya Logistik Utama No. 1, ' . $c->kota }}
                    </div>
                    <small>📞 Telepon/WA: <strong>{{ $c->no_hp ?? '021-5550100' }}</strong></small>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="pub-footer">
        <div class="pub-footer-inner">
            <div>
                <h4>PT FleetFlow Logistik Nusantara</h4>
                <p style="font-size:0.86rem; line-height:1.6;">
                    Kantor Pusat: Gedung FleetFlow Tower Lt. 5, Jl. Jend. Sudirman Kav. 45, Jakarta Selatan 12930.<br>
                    Email: info@fleetflow.co.id · Call Center: (021) 500-8899
                </p>
            </div>
            <div>
                <h4>Jam Operasional</h4>
                <p style="font-size:0.86rem; line-height:1.6;">
                    Senin – Jumat: 08.00 – 18.00 WIB<br>
                    Sabtu: 08.00 – 15.00 WIB<br>
                    Penjadwalan & Pelacakan Online: 24 Jam
                </p>
            </div>
            <div>
                <h4>Akses Sistem Internal</h4>
                <p style="font-size:0.86rem; line-height:1.6; margin-bottom:0.75rem;">
                    Khusus Admin Pusat, Staf Keuangan, Admin Cabang, dan Mitra Sopir:
                </p>
                <a href="{{ route('login') }}" class="btn btn-sm">Masuk ke FleetFlow ERP &rarr;</a>
            </div>
        </div>
        <div style="max-width:1140px; margin:2rem auto 0; padding-top:1.25rem; border-top:1px solid rgba(255,255,255,0.1); text-align:center; font-size:0.8rem;">
            &copy; {{ date('Y') }} FleetFlow Logistics ERP. Dibangun dengan Laravel 12.
        </div>
    </footer>
</body>
</html>
