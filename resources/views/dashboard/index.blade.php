@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom: 1.5rem;">Dashboard {{ ucfirst(str_replace('_', ' ', $role)) }}</h2>
    <p style="margin-bottom: 1.5rem; color: var(--text-light);">Lokasi: {{ $cabang }}</p>

    <div class="grid-cards">
        <div class="stat-card">
            <h3>Total Pesanan</h3>
            <div class="value">{{ $stats['total_pesanan'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Pengiriman Aktif</h3>
            <div class="value">{{ $stats['pengiriman_aktif'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Kendaraan Tersedia</h3>
            <div class="value">{{ $stats['kendaraan_tersedia'] }}</div>
        </div>
    </div>
@endsection