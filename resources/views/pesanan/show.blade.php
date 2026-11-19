@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Detail Pesanan: {{ $pesanan->kode_resi }}</h2>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('pesanan.resi.pdf', $pesanan) }}" class="btn btn-sm" style="background:#0F766E;">Cetak Label Resi (PDF)</a>
        <a href="{{ route('pesanan.index') }}" class="btn btn-sm">Kembali</a>
    </div>
</div>

<div class="grid-cards" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <h3>Pengirim ({{ $pesanan->kota_asal }})</h3>
        <div><strong>{{ $pesanan->nama_pengirim }}</strong></div>
        <small>{{ $pesanan->alamat_asal }}</small>
    </div>
    <div class="stat-card">
        <h3>Penerima ({{ $pesanan->kota_tujuan }})</h3>
        <div><strong>{{ $pesanan->nama_penerima }}</strong></div>
        <small>{{ $pesanan->alamat_tujuan }}</small>
    </div>
    <div class="stat-card">
        <h3>Muatan & Tarif</h3>
        <div class="value" style="font-size:1.25rem;">{{ number_format($pesanan->berat_kg, 1, ',', '.') }} kg / Rp {{ number_format($pesanan->tarif, 0, ',', '.') }}</div>
        <small>Status: <strong>{{ strtoupper(str_replace('_', ' ', $pesanan->status)) }}</strong></small>
    </div>
</div>

@if(!empty($transisi))
<div class="table-container" style="padding:1rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Ubah Status Pesanan</h3>
    <form action="{{ route('pesanan.status', $pesanan) }}" method="POST" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        @csrf
        <select name="status_baru" class="form-input" style="max-width:220px;" required>
            @foreach($transisi as $st)
                <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
            @endforeach
        </select>
        <input type="text" name="catatan" class="form-input" placeholder="Catatan / alasan..." style="max-width:350px;">
        <button type="submit" class="btn btn-sm" style="width:auto;">Perbarui Status</button>
    </form>
</div>
@endif

@if($pesanan->buktiPengirimans->isNotEmpty())
<div class="table-container" style="padding:1rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Bukti Pengiriman (POD)</h3>
    @foreach($pesanan->buktiPengirimans as $bukti)
        <div style="margin-bottom:0.5rem;">
            <strong>{{ $bukti->nama_penerima }}</strong> — {{ $bukti->waktu?->format('d/m/Y H:i') }}
            @if($bukti->catatan) ({{ $bukti->catatan }}) @endif
            @if($bukti->path)
                · <a href="{{ route('berkas.bukti', $bukti) }}" target="_blank" style="color:var(--primary);">Lihat Foto Bukti</a>
            @endif
        </div>
    @endforeach
</div>
@endif

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Riwayat Timeline Status</h3>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Dari</th>
                <th>Ke</th>
                <th>Catatan</th>
                <th>Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanan->statusLogs as $log)
            <tr>
                <td>{{ $log->waktu?->format('d/m/Y H:i') }}</td>
                <td>{{ $log->status_lama ? strtoupper(str_replace('_', ' ', $log->status_lama)) : '-' }}</td>
                <td><span class="badge active">{{ strtoupper(str_replace('_', ' ', $log->status_baru)) }}</span></td>
                <td>{{ $log->catatan ?? '-' }}</td>
                <td>{{ $log->user?->name ?? 'Sistem' }} ({{ $log->sumber }})</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
