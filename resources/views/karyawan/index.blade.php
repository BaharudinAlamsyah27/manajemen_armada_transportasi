@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Karyawan & Sopir</h2>
    <a href="{{ route('karyawan.create') }}" class="btn btn-sm">+ Tambah Karyawan</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>SIM</th>
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
                <td>{{ ucfirst(str_replace('_', ' ', $k->jabatan)) }}</td>
                <td>{{ $k->jenis_sim ? $k->jenis_sim . ' (s/d ' . ($k->sim_berlaku_sampai?->format('d/m/Y') ?? '-') . ')' : '-' }}</td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td><span class="badge {{ $k->aktif ? 'active' : 'inactive' }}">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>
                    <a href="{{ route('karyawan.edit', $k) }}" style="color:var(--primary); margin-right:0.5rem;">Edit</a>
                    <form action="{{ route('karyawan.destroy', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus karyawan ini?')">
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
