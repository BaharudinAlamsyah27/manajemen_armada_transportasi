@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>{{ $karyawan->exists ? 'Edit Karyawan' : 'Tambah Karyawan Baru' }}</h2>
    <a href="{{ route('karyawan.index') }}" class="btn btn-sm">Kembali</a>
</div>
<div class="table-container" style="padding:1.5rem; max-width:600px;">
    <form action="{{ $karyawan->exists ? route('karyawan.update', $karyawan) : route('karyawan.store') }}" method="POST">
        @csrf
        @if($karyawan->exists) @method('PUT') @endif
        @if(Auth::user()->hasRole('admin_pusat') && !$karyawan->exists)
        <div class="form-group">
            <label>Cabang</label>
            <select name="cabang_id" class="form-input" required>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ old('cabang_id', $karyawan->cabang_id) == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-input" value="{{ old('nama', $karyawan->nama) }}" required>
        </div>
        <div class="form-group">
            <label>Jabatan</label>
            <select name="jabatan" class="form-input" required>
                @foreach(['sopir', 'kernet', 'mekanik', 'staf_gudang', 'staf_admin'] as $j)
                    <option value="{{ $j }}" {{ old('jabatan', $karyawan->jabatan) === $j ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $j)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $karyawan->no_hp) }}">
        </div>
        <div class="form-group">
            <label>Nomor SIM (Terenkripsi)</label>
            <input type="text" name="no_sim" class="form-input" value="{{ old('no_sim', $karyawan->no_sim) }}">
        </div>
        <div class="form-group">
            <label>Jenis SIM</label>
            <select name="jenis_sim" class="form-input">
                <option value="">- Tidak Ada -</option>
                @foreach(['C', 'A', 'B1', 'B2'] as $sim)
                    <option value="{{ $sim }}" {{ old('jenis_sim', $karyawan->jenis_sim) === $sim ? 'selected' : '' }}>{{ $sim }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>SIM Berlaku Sampai</label>
            <input type="date" name="sim_berlaku_sampai" class="form-input" value="{{ old('sim_berlaku_sampai', $karyawan->sim_berlaku_sampai?->format('Y-m-d')) }}">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="aktif" value="1" {{ old('aktif', $karyawan->exists ? $karyawan->aktif : true) ? 'checked' : '' }}>
                Karyawan Aktif
            </label>
        </div>
        <button type="submit" class="btn">Simpan</button>
    </form>
</div>
@endsection
