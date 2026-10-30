@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Karyawan</h2>
    <button class="btn btn-sm">Tambah Karyawan</button>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Cabang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawans as $k)
            <tr>
                <td>{{ $k->kode }}</td>
                <td>{{ $k->nama }}</td>
                <td>{{ ucfirst($k->jabatan) }}</td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td><span class="badge {{ $k->aktif ? 'active' : 'inactive' }}">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td><a href="#" style="color:var(--primary);">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection