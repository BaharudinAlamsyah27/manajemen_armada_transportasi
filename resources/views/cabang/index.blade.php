@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Cabang</h2>
    <button class="btn btn-sm">Tambah Cabang</button>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Cabang</th>
                <th>Kota</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cabangs as $c)
            <tr>
                <td>{{ $c->kode }}</td>
                <td>{{ $c->nama }}</td>
                <td>{{ $c->kota }}</td>
                <td><span class="badge {{ $c->aktif ? 'active' : 'inactive' }}">{{ $c->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td><a href="#" style="color:var(--primary);">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection