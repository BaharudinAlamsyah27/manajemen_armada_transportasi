@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Penjadwalan & Manifest Pengiriman</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Kelola penugasan armada & sopir, pantau ritase harian, dan cetak Surat Jalan.</p>
    </div>
    <a href="{{ route('pengiriman.create') }}" class="btn btn-sm">+ Buat Manifest Baru</a>
</div>

<form action="{{ route('pengiriman.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari kode manifest (PGR-...)...">
        <select name="status" class="filter-input">
            <option value="">Semua Status</option>
            @foreach(['terjadwal', 'berjalan', 'selesai', 'batal'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ strtoupper($st) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm">Filter</button>
        @if(request()->anyFilled(['q', 'status']))
            <a href="{{ route('pengiriman.index') }}" class="btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.82rem;">Reset</a>
        @endif
    </div>
    <small style="color:var(--text-light); font-weight:600;">Total: {{ $pengirimans->count() }} manifest</small>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode Manifest</th>
                <th>Tanggal & Rit</th>
                <th>Kendaraan</th>
                <th>Sopir</th>
                <th>Rute Tujuan</th>
                <th>Muatan</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengirimans as $p)
            <tr>
                <td><span class="code-pill">{{ $p->kode }}</span></td>
                <td>
                    <strong>{{ $p->tanggal_rencana?->format('d/m/Y') }}</strong><br>
                    <small style="color:var(--text-light);">Rit #{{ $p->rit_ke }}</small>
                </td>
                <td><strong>{{ $p->kendaraan?->plat_nomor ?? '-' }}</strong></td>
                <td>{{ $p->sopir?->nama ?? '-' }}</td>
                <td>{{ $p->tujuan_ringkas ?? '-' }}</td>
                <td>{{ $p->pesanans->count() }} resi ({{ number_format($p->pesanans->sum('berat_kg'), 1, ',', '.') }} kg)</td>
                <td><span class="badge {{ $p->status === 'selesai' ? 'active' : 'inactive' }}">{{ strtoupper($p->status) }}</span></td>
                <td style="text-align:right;">
                    <div class="action-group">
                        <a href="{{ route('pengiriman.show', $p) }}" class="btn-action btn-edit">Detail / Eksekusi</a>
                        <a href="{{ route('pengiriman.surat-jalan.pdf', $p) }}" class="btn-action btn-success-soft">Surat Jalan</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:var(--text-light); padding:2rem;">Belum ada jadwal pengiriman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
