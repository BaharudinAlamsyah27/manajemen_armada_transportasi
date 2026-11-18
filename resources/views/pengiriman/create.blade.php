@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Jadwalkan Manifest Pengiriman Baru</h2>
    <a href="{{ route('pengiriman.index') }}" class="btn btn-sm">Kembali</a>
</div>
<div class="table-container" style="padding:1.5rem; max-width:750px;">
    <form action="{{ route('pengiriman.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Tanggal Rencana Keberangkatan</label>
            <input type="date" name="tanggal_rencana" class="form-input" value="{{ old('tanggal_rencana', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group">
            <label>Pilih Kendaraan Tersedia</label>
            <select name="kendaraan_id" class="form-input" required>
                @foreach($kendaraans as $k)
                    <option value="{{ $k->id }}">{{ $k->plat_nomor }} ({{ strtoupper($k->jenis) }} - Kapasitas {{ number_format($k->kapasitas_kg, 0, ',', '.') }} kg)</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Pilih Sopir Aktif</label>
            <select name="sopir_id" class="form-input" required>
                @foreach($sopirs as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} (SIM {{ $s->jenis_sim ?? '-' }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Pilih Pesanan (Status: Menunggu Jadwal)</label>
            <div style="border:1px solid #CBD5E1; border-radius:0.5rem; padding:0.75rem; max-height:220px; overflow-y:auto;">
                @forelse($pesanans as $ps)
                    <label style="display:block; margin-bottom:0.5rem;">
                        <input type="checkbox" name="pesanan_ids[]" value="{{ $ps->id }}">
                        <strong>{{ $ps->kode_resi }}</strong> — {{ $ps->nama_penerima }} ({{ $ps->kota_tujuan }}) — {{ number_format($ps->berat_kg, 1, ',', '.') }} kg
                    </label>
                @empty
                    <p style="color:#64748B; margin:0;">Tidak ada pesanan berstatus menunggu jadwal.</p>
                @endforelse
            </div>
        </div>
        @if(Auth::user()->hasRole('admin_pusat'))
        <div class="form-group">
            <label>Alasan Override Dokumen (Opsional, khusus Admin Pusat)</label>
            <input type="text" name="override_alasan" class="form-input" value="{{ old('override_alasan') }}">
        </div>
        @endif
        <button type="submit" class="btn">Simpan & Jadwalkan Manifest</button>
    </form>
</div>
@endsection
