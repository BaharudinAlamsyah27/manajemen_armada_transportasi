@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom: 0.5rem;">Dashboard Operasional ({{ ucfirst(str_replace('_', ' ', $role)) }})</h2>
    <p style="margin-bottom: 1.5rem; color: var(--text-light);">Cakupan Lokasi: <strong>{{ $cabang }}</strong></p>

    <div class="grid-cards" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <h3>Total Pesanan</h3>
            <div class="value">{{ $stats['total_pesanan'] }}</div>
            <small>Menunggu Jadwal: {{ $stats['pesanan_menunggu'] }}</small>
        </div>
        <div class="stat-card">
            <h3>Pengiriman Aktif</h3>
            <div class="value">{{ $stats['pengiriman_aktif'] }}</div>
            <small>Terjadwal & Berjalan</small>
        </div>
        <div class="stat-card">
            <h3>Status Armada</h3>
            <div class="value">{{ $stats['kendaraan_tersedia'] }} Tersedia</div>
            <small>Bertugas: {{ $stats['kendaraan_bertugas'] }} | Servis: {{ $stats['kendaraan_servis'] }}</small>
        </div>
        <div class="stat-card">
            <h3>Peringatan Dokumen (&le;30 Hari)</h3>
            <div class="value">{{ $stats['dokumen_peringatan'] }}</div>
            <small>STNK / KIR / Pajak / SIM</small>
        </div>
        <div class="stat-card">
            <h3>Biaya Armada Bulan Ini</h3>
            <div class="value" style="font-size:1.35rem;">Rp {{ number_format($stats['biaya_bulan_ini'], 0, ',', '.') }}</div>
            <small>BBM + Servis + Operasional</small>
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
                    <td>{{ $p->kode }}</td>
                    <td>{{ $p->tanggal_rencana?->format('d/m/Y') }}</td>
                    <td>{{ $p->cabang?->nama ?? '-' }}</td>
                    <td>{{ $p->kendaraan?->plat_nomor ?? '-' }}</td>
                    <td>{{ $p->sopir?->nama ?? '-' }}</td>
                    <td><span class="badge active">{{ strtoupper($p->status) }}</span></td>
                    <td><a href="{{ route('pengiriman.show', $p) }}" style="color:var(--primary);">Detail</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Belum ada pengiriman aktif saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
