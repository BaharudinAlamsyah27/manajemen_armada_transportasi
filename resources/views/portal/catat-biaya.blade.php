@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Catat Biaya Jalan</h2>
@if($kendaraans->isEmpty())
    <div class="kartu">Anda tidak memiliki kendaraan yang ditugaskan dalam 7 hari terakhir.</div>
@else
<form action="{{ route('portal.biaya.simpan') }}" method="POST" enctype="multipart/form-data" class="kartu">
    @csrf
    <div class="form-group">
        <label>Kendaraan</label>
        <select name="kendaraan_id" class="form-input" required>
            @foreach($kendaraans as $k)
                <option value="{{ $k->id }}" @selected(old('kendaraan_id') == $k->id)>{{ $k->plat_nomor }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Jenis</label>
        <select name="jenis" class="form-input" required>
            <option value="tol">Tol</option>
            <option value="parkir">Parkir</option>
            <option value="lainnya">Lainnya</option>
        </select>
    </div>
    <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-input" value="{{ old('tanggal', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
    </div>
    <div class="form-group">
        <label>Jumlah (Rp)</label>
        <input type="number" name="jumlah" class="form-input" inputmode="numeric" value="{{ old('jumlah') }}" required>
    </div>
    <div class="form-group">
        <label>Keterangan (opsional)</label>
        <input type="text" name="keterangan" class="form-input" value="{{ old('keterangan') }}">
    </div>
    <div class="form-group">
        <label>Foto bukti (JPG/PNG, maks 2 MB)</label>
        <input type="file" name="foto" accept="image/jpeg,image/png" capture="environment" class="form-input" required>
    </div>
    <button type="submit" class="btn-besar btn-biru">Kirim Biaya Jalan</button>
</form>
@endif
@endsection
