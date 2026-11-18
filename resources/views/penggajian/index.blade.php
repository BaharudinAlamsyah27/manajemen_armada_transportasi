@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Penggajian Karyawan</h2>
</div>

<div class="table-container" style="padding:1.25rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Hitung Penggajian Bulanan</h3>
    <form action="{{ route('penggajian.hitung') }}" method="POST" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:end;">
        @csrf
        <div><label>Cabang</label><select name="cabang_id" class="form-input" required>@foreach($cabangs as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach</select></div>
        <div><label>Bulan</label><input type="number" name="bulan" class="form-input" min="1" max="12" value="{{ now()->month }}" required></div>
        <div><label>Tahun</label><input type="number" name="tahun" class="form-input" value="{{ now()->year }}" required></div>
        <div><button type="submit" class="btn btn-sm" style="width:auto;">Hitung Slip Gaji</button></div>
    </form>
</div>

@foreach($periodes as $pr)
<div class="table-container" style="margin-bottom:1.5rem; padding:1rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
        <h3>Periode {{ $pr->bulan }}/{{ $pr->tahun }} — Status: <span class="badge active">{{ strtoupper($pr->status) }}</span></h3>
        <div>
            @if($pr->status === 'dihitung')
                <form action="{{ route('penggajian.setujui', $pr) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="width:auto;">Setujui (Maker-Checker)</button>
                </form>
            @elseif($pr->status === 'disetujui')
                <form action="{{ route('penggajian.bayar', $pr) }}" method="POST" style="display:inline-flex; gap:0.5rem;">
                    @csrf
                    <select name="akun_kas_id" class="form-input" style="width:auto;">@foreach($akunKas as $ak)<option value="{{ $ak->id }}">{{ $ak->nama }}</option>@endforeach</select>
                    <button type="submit" class="btn btn-sm" style="width:auto; background:#16A34A;">Bayar & Publikasikan</button>
                </form>
            @endif
        </div>
    </div>
    <table>
        <thead><tr><th>No Slip</th><th>Karyawan</th><th>Hadir</th><th>Ritase</th><th>Pendapatan</th><th>Potongan</th><th>Gaji Bersih</th></tr></thead>
        <tbody>
            @foreach($pr->slips as $s)
            <tr>
                <td>{{ $s->no_slip }}</td>
                <td>{{ $s->karyawan?->nama }}</td>
                <td>{{ $s->hari_hadir }} hari</td>
                <td>{{ $s->jumlah_ritase }}</td>
                <td>Rp {{ number_format($s->total_pendapatan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($s->total_potongan, 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format($s->gaji_bersih, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach
@endsection
