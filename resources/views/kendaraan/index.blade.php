@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Data Armada Kendaraan</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Pantau unit kendaraan, kapasitas muatan, odometer, serta masa berlaku STNK/KIR/Pajak.</p>
    </div>
    <a href="{{ route('kendaraan.create') }}" class="btn btn-sm">+ Tambah Kendaraan</a>
</div>

<form action="{{ route('kendaraan.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari plat nomor / kode...">
        <select name="jenis" class="filter-input">
            <option value="">Semua Jenis</option>
            @foreach(['motor', 'pickup', 'box', 'truk'] as $j)
                <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ strtoupper($j) }}</option>
            @endforeach
        </select>
        <select name="status" class="filter-input">
            <option value="">Semua Status</option>
            @foreach(['tersedia', 'bertugas', 'servis', 'nonaktif'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @if(Auth::user()->hasAnyRole(['admin_pusat', 'keuangan']))
        <select name="cabang_id" class="filter-input">
            <option value="">Semua Cabang</option>
            @foreach($cabangs as $c)
                <option value="{{ $c->id }}" @selected(request('cabang_id') == $c->id)>{{ $c->nama }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="btn btn-sm">Filter</button>
        @if(request()->anyFilled(['q', 'jenis', 'status', 'cabang_id']))
            <a href="{{ route('kendaraan.index') }}" class="btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.82rem;">Reset</a>
        @endif
    </div>
    <small style="color:var(--text-light); font-weight:600;">Total: {{ $kendaraans->count() }} unit</small>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Plat Nomor</th>
                <th>Jenis & Kapasitas</th>
                <th>Odometer</th>
                <th>Dokumen (STNK / KIR)</th>
                <th>Cabang</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kendaraans as $k)
            <tr>
                <td><span class="code-pill">{{ $k->kode }}</span></td>
                <td><strong style="font-size:0.95rem;">{{ $k->plat_nomor }}</strong></td>
                <td>
                    <span class="role-pill">{{ strtoupper($k->jenis) }}</span>
                    <span style="margin-left:0.35rem; color:var(--text-light);">{{ number_format($k->kapasitas_kg, 0, ',', '.') }} kg</span>
                </td>
                <td>{{ number_format($k->odometer_terakhir, 0, ',', '.') }} km</td>
                <td>
                    <small>STNK: {{ $k->stnk_berlaku_sampai?->format('d/m/Y') ?? '-' }}</small><br>
                    <small style="color:var(--text-light);">KIR: {{ $k->kir_berlaku_sampai?->format('d/m/Y') ?? '-' }}</small>
                </td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td><span class="badge {{ $k->status == 'tersedia' ? 'active' : 'inactive' }}">{{ ucfirst($k->status) }}</span></td>
                <td style="text-align:right;">
                    <div class="action-group">
                        <a href="{{ route('kendaraan.edit', $k) }}" class="btn-action btn-edit">Edit</a>
                        <form action="{{ route('kendaraan.destroy', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus kendaraan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:var(--text-light); padding:2rem;">Tidak ada data kendaraan yang sesuai filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
