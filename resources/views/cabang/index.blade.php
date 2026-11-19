@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Data Cabang Operasional</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Kelola kantor cabang, alamat gudang, dan nomor telepon layanan.</p>
    </div>
    <a href="{{ route('cabang.create') }}" class="btn btn-sm">+ Tambah Cabang</a>
</div>

<form action="{{ route('cabang.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari kode, nama, atau kota cabang...">
        <button type="submit" class="btn btn-sm">Cari</button>
        @if(request()->filled('q'))
            <a href="{{ route('cabang.index') }}" class="btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.82rem;">Reset</a>
        @endif
    </div>
    <small style="color:var(--text-light); font-weight:600;">Total: {{ $cabangs->count() }} cabang</small>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama & Alamat Cabang</th>
                <th>Kota</th>
                <th>Telepon</th>
                <th>Armada / SDM</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cabangs as $c)
            <tr>
                <td><span class="code-pill">{{ $c->kode }}</span></td>
                <td>
                    <strong>{{ $c->nama }}</strong>
                    @if($c->alamat)<br><small style="color:var(--text-light);">{{ $c->alamat }}</small>@endif
                </td>
                <td>{{ $c->kota ?? '-' }}</td>
                <td>{{ $c->no_hp ?? '-' }}</td>
                <td>
                    <small>{{ $c->kendaraans_count ?? 0 }} unit · {{ $c->karyawans_count ?? 0 }} orang</small>
                </td>
                <td><span class="badge {{ $c->aktif ? 'active' : 'inactive' }}">{{ $c->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td style="text-align:right;">
                    <div class="action-group">
                        <a href="{{ route('cabang.edit', $c) }}" class="btn-action btn-edit">Edit</a>
                        <form action="{{ route('cabang.destroy', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus cabang ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:var(--text-light); padding:2rem;">Tidak ada cabang ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
