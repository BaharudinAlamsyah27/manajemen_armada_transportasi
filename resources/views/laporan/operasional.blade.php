@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Laporan Biaya & Performa Armada</h2>
    <form action="{{ route('laporan.operasional') }}" method="GET" style="display:flex; gap:0.5rem; align-items:center;">
        <input type="date" name="dari" value="{{ $dari }}" class="form-input" style="width:auto;">
        <span>s/d</span>
        <input type="date" name="sampai" value="{{ $sampai }}" class="form-input" style="width:auto;">
        <button type="submit" class="btn btn-sm" style="width:auto;">Filter</button>
        <a href="{{ route('laporan.operasional', ['dari' => $dari, 'sampai' => $sampai, 'export' => 'csv']) }}" class="btn btn-sm" style="width:auto; background:#0F766E;">Ekspor CSV</a>
    </form>
</div>

<div class="stat-card" style="margin-bottom:1.5rem; max-width:320px;">
    <h3>Tingkat Keberhasilan Pengiriman</h3>
    <div class="value">{{ $data['tingkat_keberhasilan'] }}%</div>
    <small>Periode {{ $dari }} s/d {{ $sampai }}</small>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Plat Nomor</th>
                <th>Cabang</th>
                <th>Pengiriman</th>
                <th>KM Tempuh</th>
                <th>BBM</th>
                <th>Servis</th>
                <th>Biaya Ops</th>
                <th>Total Biaya</th>
                <th>Biaya / KM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['per_kendaraan'] as $row)
            <tr>
                <td><strong>{{ $row['kendaraan']->plat_nomor }}</strong></td>
                <td>{{ $row['kendaraan']->cabang?->nama }}</td>
                <td>{{ $row['jumlah_pengiriman'] }}</td>
                <td>{{ number_format($row['km_tempuh'], 0, ',', '.') }} km</td>
                <td>Rp {{ number_format($row['bbm'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['servis'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['operasional'], 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format($row['total_biaya'], 0, ',', '.') }}</strong></td>
                <td>{{ $row['biaya_per_km'] !== null ? 'Rp ' . number_format($row['biaya_per_km'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
