@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Kendaraan</h2>
    <button class="btn btn-sm">Tambah Kendaraan</button>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Plat Nomor</th>
                <th>Kode</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Cabang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kendaraans as $k)
            <tr>
                <td>{{ $k->plat_nomor }}</td>
                <td>{{ $k->kode }}</td>
                <td>{{ strtoupper($k->jenis) }}</td>
                <td><span class="badge {{ $k->status == 'tersedia' ? 'active' : 'inactive' }}">{{ ucfirst($k->status) }}</span></td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td><a href="#" style="color:var(--primary);">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection