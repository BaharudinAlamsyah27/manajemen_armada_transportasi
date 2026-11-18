@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Buat Pesanan / Resi Baru</h2>
    <a href="{{ route('pesanan.index') }}" class="btn btn-sm">Kembali</a>
</div>
<div class="table-container" style="padding:1.5rem; max-width:700px;">
    <form action="{{ route('pesanan.store') }}" method="POST">
        @csrf
        @if(Auth::user()->hasRole('admin_pusat'))
        <div class="form-group">
            <label>Cabang</label>
            <select name="cabang_id" class="form-input" required>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}">{{ $c->nama }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="form-group">
            <label>Nama Pengirim</label>
            <input type="text" name="nama_pengirim" class="form-input" value="{{ old('nama_pengirim') }}" required>
        </div>
        <div class="form-group">
            <label>No. HP Pengirim (Terenkripsi)</label>
            <input type="text" name="no_hp_pengirim" class="form-input" value="{{ old('no_hp_pengirim') }}">
        </div>
        <div class="form-group">
            <label>Kota Asal</label>
            <input type="text" name="kota_asal" class="form-input" value="{{ old('kota_asal', 'Jakarta') }}" required>
        </div>
        <div class="form-group">
            <label>Alamat Asal</label>
            <textarea name="alamat_asal" class="form-input" required>{{ old('alamat_asal') }}</textarea>
        </div>
        <hr style="margin:1rem 0; border-color:#E2E8F0;">
        <div class="form-group">
            <label>Nama Penerima</label>
            <input type="text" name="nama_penerima" class="form-input" value="{{ old('nama_penerima') }}" required>
        </div>
        <div class="form-group">
            <label>No. HP Penerima (Terenkripsi)</label>
            <input type="text" name="no_hp_penerima" class="form-input" value="{{ old('no_hp_penerima') }}" required>
        </div>
        <div class="form-group">
            <label>Kota Tujuan</label>
            <input type="text" name="kota_tujuan" class="form-input" value="{{ old('kota_tujuan') }}" required>
        </div>
        <div class="form-group">
            <label>Alamat Tujuan</label>
            <textarea name="alamat_tujuan" class="form-input" required>{{ old('alamat_tujuan') }}</textarea>
        </div>
        <hr style="margin:1rem 0; border-color:#E2E8F0;">
        <div class="form-group">
            <label>Deskripsi Barang</label>
            <input type="text" name="deskripsi_barang" class="form-input" value="{{ old('deskripsi_barang') }}">
        </div>
        <div class="form-group">
            <label>Berat (kg)</label>
            <input type="number" step="0.1" name="berat_kg" class="form-input" value="{{ old('berat_kg', 10) }}" required>
        </div>
        <div class="form-group">
            <label>Jumlah Koli</label>
            <input type="number" name="jumlah_koli" class="form-input" value="{{ old('jumlah_koli', 1) }}" required>
        </div>
        <div class="form-group">
            <label>Tarif (Rp) — Kosongkan untuk hitung otomatis (Rp 5.000/kg, min Rp 25.000)</label>
            <input type="number" name="tarif" class="form-input" value="{{ old('tarif') }}">
        </div>
        <button type="submit" class="btn">Buat Resi Pesanan</button>
    </form>
</div>
@endsection
