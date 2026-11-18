@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Jadwal 7 Hari ke Depan</h2>
@forelse($mendatang as $p)
    <a href="{{ route('portal.pengiriman', $p->id) }}" class="kartu kartu-link">
        <h3>{{ $p->tanggal_rencana->format('d/m/Y') }} — {{ $p->kode }}</h3>
        <div>{{ $p->kendaraan?->plat_nomor }} · Rit #{{ $p->rit_ke }} · {{ $p->pesanans_count }} pesanan</div>
        <div>Status: <strong>{{ strtoupper($p->status) }}</strong></div>
    </a>
@empty
    <div class="kartu">Belum ada jadwal.</div>
@endforelse

<h2 style="margin:1.5rem 0 1rem;">Riwayat 30 Hari</h2>
@forelse($riwayat as $p)
    <a href="{{ route('portal.pengiriman', $p->id) }}" class="kartu kartu-link">
        <h3>{{ $p->tanggal_rencana->format('d/m/Y') }} — {{ $p->kode }}</h3>
        <div>{{ $p->kendaraan?->plat_nomor }} · {{ $p->pesanans_count }} pesanan · {{ strtoupper($p->status) }}</div>
    </a>
@empty
    <div class="kartu">Belum ada riwayat.</div>
@endforelse
{{ $riwayat->links() }}
@endsection
