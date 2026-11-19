@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Daftar Pesanan & Resi Pengiriman</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Kelola resi kargo pelanggan, pantau status pengiriman, dan cetak label resi PDF.</p>
    </div>
    <a href="{{ route('pesanan.create') }}" class="btn btn-sm">+ Buat Pesanan Baru</a>
</div>

<form action="{{ route('pesanan.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari resi, pengirim, penerima, kota...">
        <select name="status" class="filter-input">
            <option value="">Semua Status</option>
            @foreach(['draf', 'menunggu_jadwal', 'terjadwal', 'dalam_perjalanan', 'terkirim', 'gagal_kirim', 'dibatalkan'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm">Filter</button>
        @if(request()->anyFilled(['q', 'status']))
            <a href="{{ route('pesanan.index') }}" class="btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.82rem;">Reset</a>
        @endif
    </div>
    <small style="color:var(--text-light); font-weight:600;">Total: {{ $pesanans->count() }} resi</small>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode Resi</th>
                <th>Tanggal</th>
                <th>Pengirim & Asal</th>
                <th>Penerima & Tujuan</th>
                <th>Muatan</th>
                <th>Tarif</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $p)
            <tr>
                <td><span class="code-pill">{{ $p->kode_resi }}</span></td>
                <td>{{ $p->tanggal_pesan?->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $p->nama_pengirim ?? '-' }}</strong><br>
                    <small style="color:var(--text-light);">{{ $p->kota_asal ?? '-' }}</small>
                </td>
                <td>
                    <strong>{{ $p->nama_penerima }}</strong><br>
                    <small style="color:var(--text-light);">{{ $p->kota_tujuan }}</small>
                </td>
                <td>{{ number_format($p->berat_kg, 1, ',', '.') }} kg ({{ $p->jumlah_koli }} koli)</td>
                <td><strong>Rp {{ number_format($p->tarif, 0, ',', '.') }}</strong></td>
                <td><span class="badge {{ $p->status === 'terkirim' ? 'active' : 'inactive' }}">{{ strtoupper(str_replace('_', ' ', $p->status)) }}</span></td>
                <td style="text-align:right;">
                    <div class="action-group">
                        <a href="{{ route('pesanan.show', $p) }}" class="btn-action btn-edit">Detail</a>
                        <a href="{{ route('pesanan.resi.pdf', $p) }}" class="btn-action btn-success-soft">PDF</a>
                        <a href="{{ route('lacak.publik', $p->kode_resi) }}" target="_blank" class="btn-action btn-view">Lacak</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:var(--text-light); padding:2rem;">Belum ada data pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
