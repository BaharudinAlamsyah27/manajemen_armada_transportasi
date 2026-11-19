@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div>
            <h2>Dashboard Operasional</h2>
            <p style="color: var(--text-light); font-size:0.88rem;">Cakupan Lokasi: <strong>{{ $cabang }}</strong> ({{ ucfirst(str_replace('_', ' ', $role)) }})</p>
        </div>
        @if($isPusat)
        <form action="{{ route('dashboard') }}" method="GET" style="display:flex; gap:0.5rem; align-items:center;">
            <select name="cabang_id" class="form-input" style="width:auto;" onchange="this.form.submit()">
                <option value="">Semua Cabang (Konsolidasi)</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" @selected($filterCabangId == $c->id)>{{ $c->nama }}</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    <div class="grid-cards">
        <div class="stat-card">
            <h3>Total Pesanan</h3>
            <div class="value">{{ $stats['total_pesanan'] }}</div>
            <small>Menunggu Jadwal: {{ $stats['pesanan_menunggu'] }}</small>
        </div>
        <div class="stat-card ok">
            <h3>Pengiriman Aktif</h3>
            <div class="value">{{ $stats['pengiriman_aktif'] }}</div>
            <small>Terjadwal & Berjalan</small>
        </div>
        <div class="stat-card ok">
            <h3>Armada Tersedia</h3>
            <div class="value">{{ $stats['kendaraan_tersedia'] }} Unit</div>
            <small>Bertugas: {{ $stats['kendaraan_bertugas'] }} | Servis: {{ $stats['kendaraan_servis'] }}</small>
        </div>
        <div class="stat-card warn">
            <h3>Dokumen &le;30 Hari</h3>
            <div class="value">{{ $stats['dokumen_peringatan'] }}</div>
            <small>STNK / KIR / Pajak / SIM</small>
        </div>
        <div class="stat-card">
            <h3>Biaya Armada Bulan Ini</h3>
            <div class="value" style="font-size:1.35rem;">Rp {{ number_format($stats['biaya_bulan_ini'], 0, ',', '.') }}</div>
            <small>BBM + Servis + Operasional</small>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <h3>Tren Pengiriman 7 Hari Terakhir</h3>
            <div style="height:230px; position:relative;">
                <canvas id="chartPengiriman"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Komposisi Biaya Armada Bulan Ini (Rp)</h3>
            <div style="height:230px; position:relative;">
                <canvas id="chartBiaya"></canvas>
            </div>
        </div>
    </div>

    <div class="page-header">
        <h3>Pengiriman Terjadwal & Sedang Berjalan</h3>
        <a href="{{ route('pengiriman.create') }}" class="btn btn-sm">+ Jadwalkan Manifest</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode Manifest</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>Kendaraan</th>
                    <th>Sopir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengirimanBerjalan as $p)
                <tr>
                    <td><strong>{{ $p->kode }}</strong></td>
                    <td>{{ $p->tanggal_rencana?->format('d/m/Y') }}</td>
                    <td>{{ $p->cabang?->nama ?? '-' }}</td>
                    <td>{{ $p->kendaraan?->plat_nomor ?? '-' }}</td>
                    <td>{{ $p->sopir?->nama ?? '-' }}</td>
                    <td><span class="badge active">{{ strtoupper($p->status) }}</span></td>
                    <td><a href="{{ route('pengiriman.show', $p) }}" style="color:var(--primary); font-weight:600;">Detail</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:var(--text-light);">Belum ada pengiriman aktif saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('chartPengiriman'), {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Jumlah Manifest',
                    data: @json($chartPengiriman),
                    backgroundColor: 'rgba(37, 99, 235, 0.85)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        const biayaData = @json(array_values($chartBiaya));
        const totalBiaya = biayaData.reduce((a, b) => a + b, 0);
        new Chart(document.getElementById('chartBiaya'), {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($chartBiaya)),
                datasets: [{
                    data: totalBiaya > 0 ? biayaData : [1, 1, 1],
                    backgroundColor: totalBiaya > 0
                        ? ['#2563EB', '#F59E0B', '#10B981']
                        : ['#E2E8F0', '#CBD5E1', '#F1F5F9']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        enabled: totalBiaya > 0
                    }
                }
            }
        });
    </script>
@endsection
