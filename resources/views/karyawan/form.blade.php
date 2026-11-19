@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>{{ $karyawan->exists ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Lengkapi profil personel, jabatan, dan dokumen SIM (khusus sopir).</p>
    </div>
    <a href="{{ route('karyawan.index') }}" class="btn-secondary">&larr; Kembali ke Daftar</a>
</div>

<div class="form-card">
    <form action="{{ $karyawan->exists ? route('karyawan.update', $karyawan) : route('karyawan.store') }}" method="POST">
        @csrf
        @if($karyawan->exists) @method('PUT') @endif

        <div class="form-card-section">1. Identitas & Penempatan</div>
        <div class="form-grid-2">
            @if(Auth::user()->hasRole('admin_pusat') && !$karyawan->exists)
            <div class="form-group">
                <label>Cabang Penempatan</label>
                <select name="cabang_id" class="form-input" required>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ old('cabang_id', $karyawan->cabang_id) == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" value="{{ old('nama', $karyawan->nama) }}" placeholder="Contoh: Budi Santoso" required>
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
                <label>Nomor HP / WhatsApp</label>
                <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $karyawan->no_hp) }}" placeholder="0812xxxxxxxx">
            </div>
        </div>

        <div class="form-card-section" style="margin-top:1.25rem;">2. Dokumen SIM (Khusus Sopir — Disimpan Terenkripsi)</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nomor SIM</label>
                <input type="text" name="no_sim" class="form-input" value="{{ old('no_sim', $karyawan->no_sim) }}" placeholder="Opsional untuk non-sopir">
            </div>
            <div class="form-group">
                <label>Golongan SIM</label>
                <select name="jenis_sim" class="form-input">
                    <option value="">- Tidak Ada -</option>
                    @foreach(['C', 'A', 'B1', 'B2'] as $sim)
                        <option value="{{ $sim }}" {{ old('jenis_sim', $karyawan->jenis_sim) === $sim ? 'selected' : '' }}>SIM {{ $sim }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Masa Berlaku SIM Sampai</label>
                <input type="date" name="sim_berlaku_sampai" class="form-input" value="{{ old('sim_berlaku_sampai', $karyawan->sim_berlaku_sampai?->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="display:flex; align-items:center; padding-top:1.5rem;">
                <label style="display:inline-flex; align-items:center; gap:0.5rem; cursor:pointer; margin:0;">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', $karyawan->exists ? $karyawan->aktif : true) ? 'checked' : '' }}>
                    <span>Status Karyawan Aktif</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('karyawan.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn btn-sm">Simpan Data Karyawan</button>
        </div>
    </form>
</div>
@endsection
