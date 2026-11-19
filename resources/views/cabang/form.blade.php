@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>{{ $cabang->exists ? 'Edit Data Cabang' : 'Tambah Cabang Baru' }}</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Informasi cabang akan ditampilkan juga pada halaman layanan publik.</p>
    </div>
    <a href="{{ route('cabang.index') }}" class="btn-secondary">&larr; Kembali ke Daftar</a>
</div>

<div class="form-card">
    <form action="{{ $cabang->exists ? route('cabang.update', $cabang) : route('cabang.store') }}" method="POST">
        @csrf
        @if($cabang->exists) @method('PUT') @endif

        <div class="form-card-section">Informasi Kantor Cabang</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Kode Cabang (3-5 Huruf)</label>
                <input type="text" name="kode" class="form-input" value="{{ old('kode', $cabang->kode) }}" placeholder="JKT / BDG / SBY" required>
            </div>
            <div class="form-group">
                <label>Nama Cabang</label>
                <input type="text" name="nama" class="form-input" value="{{ old('nama', $cabang->nama) }}" placeholder="Cabang Jakarta" required>
            </div>
            <div class="form-group">
                <label>Kota</label>
                <input type="text" name="kota" class="form-input" value="{{ old('kota', $cabang->kota) }}" placeholder="Jakarta">
            </div>
            <div class="form-group">
                <label>Nomor Telepon / WhatsApp</label>
                <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $cabang->no_hp) }}" placeholder="021-5008891">
            </div>
        </div>
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" class="form-input" rows="2" placeholder="Jl. Raya Logistik No. 10...">{{ old('alamat', $cabang->alamat) }}</textarea>
        </div>
        <div class="form-group">
            <label style="display:inline-flex; align-items:center; gap:0.5rem; cursor:pointer;">
                <input type="checkbox" name="aktif" value="1" {{ old('aktif', $cabang->exists ? $cabang->aktif : true) ? 'checked' : '' }}>
                <span>Cabang Beroperasi Aktif</span>
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('cabang.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn btn-sm">Simpan Data Cabang</button>
        </div>
    </form>
</div>
@endsection
