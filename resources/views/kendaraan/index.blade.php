@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Data Armada Kendaraan</h2>
    <a href="{{ route('kendaraan.create') }}" class="btn btn-sm">+ Tambah Kendaraan</a>
</div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Plat Nomor</th>
                <th>Kode</th>
                <th>Jenis</th>
                <th>Kapasitas</th>
                <th>Odometer</th>
                <th>Status</th>
                <th>Cabang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kendaraans as $k)
            <tr>
                <td><strong>{{ $k->plat_nomor }}</strong></td>
                <td>{{ $k->kode }}</td>
                <td>{{ strtoupper($k->jenis) }}</td>
                <td>{{ number_format($k->kapasitas_kg, 0, ',', '.') }} kg</td>
                <td>{{ number_format($k->odometer_terakhir, 0, ',', '.') }} km</td>
                <td><span class="badge {{ $k->status == 'tersedia' ? 'active' : 'inactive' }}">{{ ucfirst($k->status) }}</span></td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td>
                    <a href="{{ route('kendaraan.edit', $k) }}" style="color:var(--primary); margin-right:0.5rem;">Edit</a>
                    <form action="{{ route('kendaraan.destroy', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus kendaraan ini?')">
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
