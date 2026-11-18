@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Slip Gaji Saya</h2>
@forelse($slips as $slip)
    <div class="kartu">
        <h3>{{ $slip->no_slip }} — Periode {{ $slip->periodeGaji->bulan }}/{{ $slip->periodeGaji->tahun }}</h3>
        <div>Hadir: {{ $slip->hari_hadir }} hari · Ritase: {{ $slip->jumlah_ritase }}</div>
        <div style="margin:0.5rem 0;">
            @foreach($slip->items as $it)
                <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                    <span>{{ $it->keterangan }} ({{ $it->jenis }})</span>
                    <span>Rp {{ number_format($it->jumlah, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
        <hr style="border-color:#E2E8F0; margin:0.5rem 0;">
        <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.05rem;">
            <span>Gaji Bersih</span>
            <span>Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</span>
        </div>
    </div>
@empty
    <div class="kartu">Belum ada slip gaji yang dipublikasikan.</div>
@endforelse
@endsection
