@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Pengumuman</h2>
@forelse($pengumumans as $p)
    <div class="kartu" style="{{ $p->kategori === 'darurat' ? 'border:2px solid #DC2626; background:#FEF2F2;' : '' }}">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
            <span class="badge {{ $p->kategori === 'darurat' ? 'inactive' : 'active' }}">{{ strtoupper($p->kategori) }}</span>
            <small style="color:var(--text-light);">{{ $p->terbit_pada?->format('d/m/Y') }}</small>
        </div>
        <h3>{{ $p->judul }}</h3>
        <p style="white-space:pre-line;">{{ $p->isi }}</p>
    </div>
@empty
    <div class="kartu">Belum ada pengumuman untuk Anda.</div>
@endforelse
@endsection
