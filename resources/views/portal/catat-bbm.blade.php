@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Catat Pengisian BBM</h2>
@if($kendaraans->isEmpty())
    <div class="kartu">Anda tidak memiliki kendaraan yang ditugaskan dalam 7 hari terakhir.</div>
@else
<form action="{{ route('portal.bbm.simpan') }}" method="POST" enctype="multipart/form-data" class="kartu">
    @csrf
    <div class="form-group">
        <label>Kendaraan</label>
        <select name="kendaraan_id" class="form-input" required>
            @foreach($kendaraans as $k)
                <option value="{{ $k->id }}" @selected(old('kendaraan_id') == $k->id)>{{ $k->plat_nomor }} ({{ $k->odometer_terakhir }} km)</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-input" value="{{ old('tanggal', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
    </div>
    <div class="form-group">
        <label>Liter</label>
        <input type="number" step="0.01" name="liter" class="form-input" inputmode="decimal" value="{{ old('liter') }}" required>
    </div>
    <div class="form-group">
        <label>Harga per liter (Rp)</label>
        <input type="number" name="harga_per_liter" class="form-input" inputmode="numeric" value="{{ old('harga_per_liter') }}" required>
    </div>
    <div class="form-group">
        <label>Odometer (km)</label>
        <input type="number" name="odometer" class="form-input" inputmode="numeric" value="{{ old('odometer') }}" required>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="tangki_penuh" value="1" @checked(old('tangki_penuh'))> Tangki diisi penuh</label>
    </div>
    <div class="form-group">
        <label>SPBU (opsional)</label>
        <input type="text" name="spbu" class="form-input" value="{{ old('spbu') }}">
    </div>
    <div class="form-group">
        <label>Foto struk (JPG/PNG, maks 2 MB)</label>
        <input type="file" name="foto" accept="image/jpeg,image/png" capture="environment" class="form-input" required>
    </div>
    <p style="color:var(--text-light); margin-bottom:0.75rem;">Total dihitung otomatis. Data akan diperiksa admin cabang sebelum dicatat.</p>
    <button type="submit" class="btn-besar btn-biru">Kirim Catatan BBM</button>
</form>
@endif
@endsection
