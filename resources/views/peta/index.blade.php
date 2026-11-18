@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Peta Armada & Pelacakan GPS</h2>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<div id="map" style="height:420px; border-radius:0.75rem; border:1px solid var(--border); margin-bottom:1.5rem;"></div>

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Armada Sedang Berjalan ({{ $pengirimanBerjalan->count() }})</h3>
    <table>
        <thead><tr><th>Manifest</th><th>Kendaraan</th><th>Sopir</th><th>Cabang</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($pengirimanBerjalan as $p)
            <tr>
                <td>{{ $p->kode }}</td>
                <td>{{ $p->kendaraan?->plat_nomor }}</td>
                <td>{{ $p->sopir?->nama }}</td>
                <td>{{ $p->cabang?->nama }}</td>
                <td><span class="badge active">{{ strtoupper($p->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;">Tidak ada armada sedang berjalan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([-6.200000, 106.816666], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    const sesis = @json($sesis);
    sesis.forEach(s => {
        if (s.lat && s.lng) {
            L.marker([s.lat, s.lng]).addTo(map)
                .bindPopup(`<strong>${s.kendaraan}</strong><br>Sopir: ${s.sopir}<br>Manifest: ${s.kode}<br>Update: ${s.waktu}`);
        }
    });
</script>
@endsection
