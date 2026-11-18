@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Daftar Pesanan & Resi</h2>
    <a href="{{ route('pesanan.create') }}" class="btn btn-sm">+ Buat Pesanan</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode Resi</th>
                <th>Tanggal</th>
                <th>Pengirim</th>
                <th>Penerima & Tujuan</th>
                <th>Berat</th>
                <th>Tarif</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $p)
            <tr>
                <td><strong>{{ $p->kode_resi }}</strong></td>
                <td>{{ $p->tanggal_pesan?->format('d/m/Y') }}</td>
                <td>{{ $p->nama_pengirim ?? '-' }} ({{ $p->kota_asal ?? '-' }})</td>
                <td>{{ $p->nama_penerima }} ({{ $p->kota_tujuan }})</td>
                <td>{{ number_format($p->berat_kg, 1, ',', '.') }} kg ({{ $p->jumlah_koli }} koli)</td>
                <td>Rp {{ number_format($p->tarif, 0, ',', '.') }}</td>
                <td><span class="badge active">{{ strtoupper(str_replace('_', ' ', $p->status)) }}</span></td>
                <td>
                    <a href="{{ route('pesanan.show', $p) }}" style="color:var(--primary); margin-right:0.5rem;">Detail</a>
                    <a href="{{ route('lacak.publik', $p->kode_resi) }}" target="_blank" style="color:#64748B;">Lacak</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;">Belum ada data pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
