@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Jadwalkan Manifest Pengiriman Baru</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Sistem memvalidasi kapasitas muatan, dokumen STNK/KIR/Pajak, kesesuaian SIM, dan bentrok jadwal.</p>
    </div>
    <a href="{{ route('pengiriman.index') }}" class="btn-secondary">&larr; Kembali ke Daftar</a>
</div>

<div class="form-card">
    <form action="{{ route('pengiriman.store') }}" method="POST">
        @csrf
        <div class="form-card-section">1. Penugasan Armada & Sopir</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label>Tanggal Rencana Keberangkatan</label>
                <input type="date" name="tanggal_rencana" class="form-input" value="{{ old('tanggal_rencana', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Kendaraan Tersedia</label>
                <select name="kendaraan_id" class="form-input" required>
                    @foreach($kendaraans as $k)
                        <option value="{{ $k->id }}">{{ $k->plat_nomor }} — {{ strtoupper($k->jenis) }} (Maks {{ number_format($k->kapasitas_kg, 0, ',', '.') }} kg)</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Sopir Bertugas</label>
                <select name="sopir_id" class="form-input" required>
                    @foreach($sopirs as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }} (SIM {{ $s->jenis_sim ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            @if(Auth::user()->hasRole('admin_pusat'))
            <div class="form-group">
                <label>Alasan Override Dokumen (Khusus Admin Pusat)</label>
                <input type="text" name="override_alasan" class="form-input" placeholder="Opsional bila dokumen kedaluwarsa" value="{{ old('override_alasan') }}">
            </div>
            @endif
        </div>

        <div class="form-card-section" style="margin-top:1.25rem;">2. Pilih Resi Pesanan (Status: Menunggu Jadwal)</div>
        <div style="border:1px solid var(--border); border-radius:0.65rem; padding:1rem; max-height:260px; overflow-y:auto; background:#F8FAFC;">
            @forelse($pesanans as $ps)
                <label style="display:flex; align-items:center; justify-content:space-between; padding:0.55rem 0.75rem; background:#fff; border:1px solid var(--border); border-radius:0.5rem; margin-bottom:0.5rem; cursor:pointer;">
                    <span style="display:flex; align-items:center; gap:0.6rem;">
                        <input type="checkbox" name="pesanan_ids[]" value="{{ $ps->id }}">
                        <span class="code-pill">{{ $ps->kode_resi }}</span>
                        <strong>{{ $ps->nama_penerima }}</strong>
                        <small style="color:var(--text-light);">({{ $ps->kota_tujuan }})</small>
                    </span>
                    <span class="role-pill">{{ number_format($ps->berat_kg, 1, ',', '.') }} kg</span>
                </label>
            @empty
                <p style="color:var(--text-light); margin:0; text-align:center;">Tidak ada pesanan berstatus menunggu jadwal.</p>
            @endforelse
        </div>

        <div class="form-actions">
            <a href="{{ route('pengiriman.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn btn-sm">Simpan & Jadwalkan Manifest</button>
        </div>
    </form>
</div>
@endsection
