@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Absensi Harian Karyawan</h2>
    <form action="{{ route('absensi.index') }}" method="GET" style="display:flex; gap:0.5rem; align-items:center;">
        <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input" style="width:auto;">
        <button type="submit" class="btn btn-sm" style="width:auto;">Tampilkan</button>
    </form>
</div>

<div class="table-container">
    <form action="{{ route('absensi.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        <table>
            <thead>
                <tr>
                    <th>Kode & Nama</th>
                    <th>Jabatan</th>
                    <th>Cabang</th>
                    <th>Status ({{ $tanggal }})</th>
                    <th>Catatan</th>
                    <th>Hadir Bulan Ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach($karyawans as $k)
                @php
                    $existing = $absensiHariIni->get($k->id);
                    $hadirBulan = $rekapBulan->get($k->id)?->firstWhere('status', 'hadir')?->total ?? 0;
                @endphp
                <tr>
                    <td><strong>{{ $k->nama }}</strong><br><small>{{ $k->kode }}</small></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $k->jabatan)) }}</td>
                    <td>{{ $k->cabang?->nama ?? '-' }}</td>
                    <td>
                        <select name="status[{{ $k->id }}]" class="form-input" style="padding:0.4rem;">
                            @foreach(['hadir', 'izin', 'sakit', 'alpa', 'libur'] as $st)
                                <option value="{{ $st }}" @selected(($existing?->status ?? 'hadir') === $st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="catatan[{{ $k->id }}]" value="{{ $existing?->catatan }}" class="form-input" style="padding:0.4rem;"></td>
                    <td><strong>{{ $hadirBulan }} hari</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:1rem;">
            <button type="submit" class="btn btn-sm" style="width:auto;">Simpan Absensi Tanggal {{ $tanggal }}</button>
        </div>
    </form>
</div>
@endsection
