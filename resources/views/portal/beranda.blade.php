@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:0.25rem;">Halo, {{ $karyawan->nama }}</h2>
<p style="color:var(--text-light); margin-bottom:1rem;">{{ now()->translatedFormat('l, d F Y') }}</p>

@if($simSegeraHabis)
    <div class="peringatan">SIM Anda berlaku sampai <strong>{{ $karyawan->sim_berlaku_sampai->format('d/m/Y') }}</strong>. Segera perpanjang.</div>
@endif

<h3 style="margin-bottom:0.75rem;">Pengiriman Hari Ini</h3>
@forelse($hariIni as $p)
    <a href="{{ route('portal.pengiriman', $p->id) }}" class="kartu kartu-link">
        <h3>{{ $p->kode }} <span class="badge {{ $p->status === 'berjalan' ? 'active' : 'inactive' }}">{{ strtoupper($p->status) }}</span></h3>
        <div>Kendaraan: <strong>{{ $p->kendaraan?->plat_nomor }}</strong></div>
        <div>Jam mulai: {{ $p->waktu_mulai_rencana?->format('H:i') ?? '-' }} · Rit #{{ $p->rit_ke }}</div>
        <div>{{ $p->pesanans_count }} pesanan · {{ $p->tujuan_ringkas ?? '-' }}</div>
    </a>
@empty
    <div class="kartu">Tidak ada pengiriman untuk hari ini.</div>
@endforelse

<div class="baris-aksi" style="margin-top:1rem;">
    <a href="{{ route('portal.bbm') }}" class="btn-besar btn-biru">Catat BBM</a>
    <a href="{{ route('portal.biaya') }}" class="btn-besar btn-abu">Catat Biaya Jalan</a>
</div>
@endsection
