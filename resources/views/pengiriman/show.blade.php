@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Detail Manifest: {{ $pengiriman->kode }}</h2>
    <a href="{{ route('pengiriman.index') }}" class="btn btn-sm">Kembali</a>
</div>

<div class="grid-cards" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <h3>Kendaraan & Sopir</h3>
        <div><strong>{{ $pengiriman->kendaraan?->plat_nomor }}</strong> ({{ strtoupper($pengiriman->kendaraan?->jenis ?? '') }})</div>
        <small>Sopir: {{ $pengiriman->sopir?->nama }}</small>
    </div>
    <div class="stat-card">
        <h3>Jadwal & Rit</h3>
        <div><strong>{{ $pengiriman->tanggal_rencana?->format('d/m/Y') }} (Rit #{{ $pengiriman->rit_ke }})</strong></div>
        <small>Status: {{ strtoupper($pengiriman->status) }}</small>
    </div>
    <div class="stat-card">
        <h3>Odometer & Muatan</h3>
        <div>Awal: {{ $pengiriman->odometer_awal ?? '-' }} km | Akhir: {{ $pengiriman->odometer_akhir ?? '-' }} km</div>
        <small>Total Muatan: {{ number_format($pengiriman->pesanans->sum('berat_kg'), 1, ',', '.') }} kg</small>
    </div>
</div>

@if($pengiriman->status === 'terjadwal')
<div class="table-container" style="padding:1rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Berangkatkan Armada / Batalkan</h3>
    <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
        <form action="{{ route('pengiriman.mulai', $pengiriman) }}" method="POST" style="display:flex; gap:0.5rem; align-items:center;">
            @csrf
            <input type="number" name="odometer_awal" class="form-input" style="max-width:180px;" value="{{ $pengiriman->kendaraan?->odometer_terakhir ?? 0 }}" required>
            <button type="submit" class="btn btn-sm" style="width:auto;">Mulai Perjalanan</button>
        </form>
        <form action="{{ route('pengiriman.batal', $pengiriman) }}" method="POST" style="display:flex; gap:0.5rem; align-items:center;" onsubmit="return confirm('Batalkan manifest ini?')">
            @csrf
            <input type="text" name="alasan" class="form-input" style="max-width:220px;" placeholder="Alasan pembatalan..." required>
            <button type="submit" class="btn btn-sm" style="width:auto; background:#DC2626;">Batalkan</button>
        </form>
    </div>
</div>
@elseif($pengiriman->status === 'berjalan')
<div class="table-container" style="padding:1rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Selesaikan Pengiriman</h3>
    <form action="{{ route('pengiriman.selesai', $pengiriman) }}" method="POST" style="display:flex; gap:0.75rem; align-items:center;">
        @csrf
        <input type="number" name="odometer_akhir" class="form-input" style="max-width:220px;" value="{{ ($pengiriman->odometer_awal ?? 0) + 25 }}" required>
        <button type="submit" class="btn btn-sm" style="width:auto;">Selesaikan Perjalanan</button>
    </form>
</div>
@endif

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Daftar Pesanan dalam Manifest Ini</h3>
    <table>
        <thead>
            <tr>
                <th>Kode Resi</th>
                <th>Penerima</th>
                <th>Kota Tujuan</th>
                <th>Berat</th>
                <th>Status</th>
                <th>Aksi Admin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengiriman->pesanans as $ps)
            <tr>
                <td><a href="{{ route('pesanan.show', $ps) }}" style="color:var(--primary); font-weight:bold;">{{ $ps->kode_resi }}</a></td>
                <td>{{ $ps->nama_penerima }}</td>
                <td>{{ $ps->kota_tujuan }}</td>
                <td>{{ number_format($ps->berat_kg, 1, ',', '.') }} kg</td>
                <td><span class="badge active">{{ strtoupper(str_replace('_', ' ', $ps->status)) }}</span></td>
                <td>
                    @if($pengiriman->status === 'berjalan' && $ps->status === 'dalam_perjalanan')
                    <form action="{{ route('pengiriman.terkirim', [$pengiriman, $ps]) }}" method="POST" style="display:inline-flex; gap:0.35rem; align-items:center; margin-bottom:0.25rem;">
                        @csrf
                        <input type="text" name="nama_penerima" placeholder="Penerima" required style="width:110px; padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                        <input type="text" name="catatan" placeholder="Catatan wajib" required style="width:120px; padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                        <button type="submit" class="btn btn-sm" style="width:auto; background:#16A34A;">Terkirim</button>
                    </form>
                    <form action="{{ route('pengiriman.gagal', [$pengiriman, $ps]) }}" method="POST" style="display:inline-flex; gap:0.35rem; align-items:center;">
                        @csrf
                        <input type="text" name="alasan" placeholder="Alasan gagal" required style="width:120px; padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                        <button type="submit" class="btn btn-sm" style="width:auto; background:#DC2626;">Gagal</button>
                    </form>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
