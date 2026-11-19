@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>{{ $kendaraan->exists ? 'Edit Data Kendaraan' : 'Tambah Kendaraan Baru' }}</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Lengkapi spesifikasi teknis armada dan tanggal jatuh tempo dokumen jalan.</p>
    </div>
    <a href="{{ route('kendaraan.index') }}" class="btn-secondary">&larr; Kembali ke Daftar</a>
</div>

<div class="form-card">
    <form action="{{ $kendaraan->exists ? route('kendaraan.update', $kendaraan) : route('kendaraan.store') }}" method="POST">
        @csrf
        @if($kendaraan->exists) @method('PUT') @endif

        <div class="form-card-section">1. Identitas & Kapasitas Armada</div>
        <div class="form-grid-2">
            @if(Auth::user()->hasRole('admin_pusat') && !$kendaraan->exists)
            <div class="form-group">
                <label>Cabang Pemilik</label>
                <select name="cabang_id" class="form-input" required>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ old('cabang_id', $kendaraan->cabang_id) == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group">
                <label>Plat Nomor</label>
                <input type="text" name="plat_nomor" class="form-input" value="{{ old('plat_nomor', $kendaraan->plat_nomor) }}" placeholder="B 1234 XYZ" required>
            </div>
            <div class="form-group">
                <label>Jenis Armada</label>
                <select name="jenis" class="form-input" required>
                    @foreach(['motor', 'pickup', 'box', 'truk'] as $j)
                        <option value="{{ $j }}" {{ old('jenis', $kendaraan->jenis) === $j ? 'selected' : '' }}>{{ strtoupper($j) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Merk / Tipe</label>
                <input type="text" name="merk" class="form-input" placeholder="Mitsubishi Colt Diesel" value="{{ old('merk', $kendaraan->merk) }}">
            </div>
            <div class="form-group">
                <label>Kapasitas Muatan Maksimal (Kg)</label>
                <input type="number" step="0.01" name="kapasitas_kg" class="form-input" value="{{ old('kapasitas_kg', $kendaraan->kapasitas_kg ?? 2000) }}" required>
            </div>
            <div class="form-group">
                <label>Odometer Terakhir (Km)</label>
                <input type="number" name="odometer_terakhir" class="form-input" value="{{ old('odometer_terakhir', $kendaraan->odometer_terakhir ?? 0) }}" required>
            </div>
            <div class="form-group">
                <label>Status Operasional</label>
                <select name="status" class="form-input" required>
                    @foreach(['tersedia', 'bertugas', 'servis', 'nonaktif'] as $st)
                        <option value="{{ $st }}" {{ old('status', $kendaraan->status ?? 'tersedia') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-card-section" style="margin-top:1.25rem;">2. Masa Berlaku Dokumen (Wajib Aktif Saat Penjadwalan)</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>STNK Berlaku Sampai</label>
                <input type="date" name="stnk_berlaku_sampai" class="form-input" value="{{ old('stnk_berlaku_sampai', $kendaraan->stnk_berlaku_sampai?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>KIR Berlaku Sampai</label>
                <input type="date" name="kir_berlaku_sampai" class="form-input" value="{{ old('kir_berlaku_sampai', $kendaraan->kir_berlaku_sampai?->format('Y-m-d') ?? now()->addMonths(6)->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Pajak Kendaraan Berlaku Sampai</label>
                <input type="date" name="pajak_berlaku_sampai" class="form-input" value="{{ old('pajak_berlaku_sampai', $kendaraan->pajak_berlaku_sampai?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('kendaraan.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn btn-sm">Simpan Data Kendaraan</button>
        </div>
    </form>
</div>
@endsection
