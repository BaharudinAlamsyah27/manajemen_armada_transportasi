@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>{{ $cabang->exists ? 'Edit Cabang' : 'Tambah Cabang Baru' }}</h2>
    <a href="{{ route('cabang.index') }}" class="btn btn-sm">Kembali</a>
</div>
<div class="table-container" style="padding:1.5rem; max-width:600px;">
    <form action="{{ $cabang->exists ? route('cabang.update', $cabang) : route('cabang.store') }}" method="POST">
        @csrf
        @if($cabang->exists) @method('PUT') @endif
        <div class="form-group">
            <label>Kode Cabang</label>
            <input type="text" name="kode" class="form-input" value="{{ old('kode', $cabang->kode) }}" required>
        </div>
        <div class="form-group">
            <label>Nama Cabang</label>
            <input type="text" name="nama" class="form-input" value="{{ old('nama', $cabang->nama) }}" required>
        </div>
        <div class="form-group">
            <label>Kota</label>
            <input type="text" name="kota" class="form-input" value="{{ old('kota', $cabang->kota) }}">
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-input">{{ old('alamat', $cabang->alamat) }}</textarea>
        </div>
        <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $cabang->no_hp) }}">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="aktif" value="1" {{ old('aktif', $cabang->exists ? $cabang->aktif : true) ? 'checked' : '' }}>
                Cabang Aktif
            </label>
        </div>
        <button type="submit" class="btn">Simpan</button>
    </form>
</div>
@endsection
