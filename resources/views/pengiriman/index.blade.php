@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Penjadwalan & Manifest Pengiriman</h2>
    <a href="{{ route('pengiriman.create') }}" class="btn btn-sm">+ Buat Manifest Baru</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode Manifest</th>
                <th>Tanggal (Rit)</th>
                <th>Kendaraan</th>
                <th>Sopir</th>
                <th>Tujuan Ringkas</th>
                <th>Jumlah Resi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengirimans as $p)
            <tr>
                <td><strong>{{ $p->kode }}</strong></td>
                <td>{{ $p->tanggal_rencana?->format('d/m/Y') }} (Rit #{{ $p->rit_ke }})</td>
                <td>{{ $p->kendaraan?->plat_nomor ?? '-' }}</td>
                <td>{{ $p->sopir?->nama ?? '-' }}</td>
                <td>{{ $p->tujuan_ringkas ?? '-' }}</td>
                <td>{{ $p->pesanans->count() }} resi ({{ number_format($p->pesanans->sum('berat_kg'), 1, ',', '.') }} kg)</td>
                <td><span class="badge {{ $p->status === 'selesai' ? 'active' : 'inactive' }}">{{ strtoupper($p->status) }}</span></td>
                <td><a href="{{ route('pengiriman.show', $p) }}" style="color:var(--primary);">Detail / Eksekusi</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;">Belum ada jadwal pengiriman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
