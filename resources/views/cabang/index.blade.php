@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Cabang</h2>
    <a href="{{ route('cabang.create') }}" class="btn btn-sm">+ Tambah Cabang</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Cabang</th>
                <th>Kota</th>
                <th>No. HP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cabangs as $c)
            <tr>
                <td>{{ $c->kode }}</td>
                <td>{{ $c->nama }}</td>
                <td>{{ $c->kota ?? '-' }}</td>
                <td>{{ $c->no_hp ?? '-' }}</td>
                <td><span class="badge {{ $c->aktif ? 'active' : 'inactive' }}">{{ $c->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>
                    <a href="{{ route('cabang.edit', $c) }}" style="color:var(--primary); margin-right:0.5rem;">Edit</a>
                    <form action="{{ route('cabang.destroy', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus cabang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
