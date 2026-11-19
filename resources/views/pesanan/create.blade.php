@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Buat Pesanan / Resi Baru</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Nomor resi acak otomatis dibuat saat disimpan. Nomor HP pengirim & penerima dienkripsi.</p>
    </div>
    <a href="{{ route('pesanan.index') }}" class="btn-secondary">&larr; Kembali ke Daftar</a>
</div>

<div class="form-card">
    <form action="{{ route('pesanan.store') }}" method="POST">
        @csrf
        @if(Auth::user()->hasRole('admin_pusat'))
        <div class="form-card-section">Cabang Pengirim</div>
        <div class="form-group">
            <label>Pilih Cabang Asal</label>
            <select name="cabang_id" class="form-input" required>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}">{{ $c->nama }} ({{ $c->kota }})</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-card-section">1. Informasi Pengirim (Asal)</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nama Pengirim / Perusahaan</label>
                <input type="text" name="nama_pengirim" class="form-input" value="{{ old('nama_pengirim') }}" required>
            </div>
            <div class="form-group">
                <label>No. HP Pengirim</label>
                <input type="text" name="no_hp_pengirim" class="form-input" value="{{ old('no_hp_pengirim') }}">
            </div>
            <div class="form-group">
                <label>Kota Asal</label>
                <input type="text" name="kota_asal" class="form-input" value="{{ old('kota_asal', 'Jakarta') }}" required>
            </div>
            <div class="form-group">
                <label>Alamat Asal Lengkap</label>
                <input type="text" name="alamat_asal" class="form-input" value="{{ old('alamat_asal') }}" required>
            </div>
        </div>

        <div class="form-card-section" style="margin-top:1.25rem;">2. Informasi Penerima (Tujuan)</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nama Penerima</label>
                <input type="text" name="nama_penerima" class="form-input" value="{{ old('nama_penerima') }}" required>
            </div>
            <div class="form-group">
                <label>No. HP Penerima</label>
                <input type="text" name="no_hp_penerima" class="form-input" value="{{ old('no_hp_penerima') }}" required>
            </div>
            <div class="form-group">
                <label>Kota Tujuan</label>
                <input type="text" name="kota_tujuan" class="form-input" value="{{ old('kota_tujuan') }}" required>
            </div>
            <div class="form-group">
                <label>Alamat Tujuan Lengkap</label>
                <input type="text" name="alamat_tujuan" class="form-input" value="{{ old('alamat_tujuan') }}" required>
            </div>
        </div>

        <div class="form-card-section" style="margin-top:1.25rem;">3. Detail Muatan & Tarif</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Deskripsi Barang</label>
                <input type="text" name="deskripsi_barang" class="form-input" placeholder="Contoh: Elektronik (10 Box)" value="{{ old('deskripsi_barang') }}">
            </div>
            <div class="form-group">
                <label>Berat Aktual (Kg)</label>
                <input type="number" step="0.1" name="berat_kg" class="form-input" value="{{ old('berat_kg', 10) }}" required>
            </div>
            <div class="form-group">
                <label>Jumlah Koli</label>
                <input type="number" name="jumlah_koli" class="form-input" value="{{ old('jumlah_koli', 1) }}" required>
            </div>
            <div class="form-group">
                <label>Tarif (Rp) — Kosongkan untuk hitung otomatis</label>
                <input type="number" name="tarif" class="form-input" placeholder="Otomatis: Rp 5.000/kg (min Rp 25.000)" value="{{ old('tarif') }}">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('pesanan.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn btn-sm">Simpan & Terbitkan Resi</button>
        </div>
    </form>
</div>
@endsection
