@extends('layouts.portal')
@section('content')
<a href="{{ route('portal.jadwal') }}" style="color:var(--primary);">&larr; Kembali</a>
<h2 style="margin:0.5rem 0;">{{ $pengiriman->kode }}</h2>
<div class="kartu">
    <div>Tanggal: <strong>{{ $pengiriman->tanggal_rencana->format('d/m/Y') }}</strong> (Rit #{{ $pengiriman->rit_ke }})</div>
    <div>Kendaraan: <strong>{{ $pengiriman->kendaraan?->plat_nomor }}</strong> — odometer terakhir {{ number_format($pengiriman->kendaraan?->odometer_terakhir ?? 0, 0, ',', '.') }} km</div>
    <div>Status: <strong>{{ strtoupper($pengiriman->status) }}</strong></div>
</div>

@if($pengiriman->status === 'terjadwal')
    <div class="kartu">
        <h3>Mulai Perjalanan</h3>
        <form action="{{ route('portal.mulai', $pengiriman->id) }}" method="POST">
            @csrf
            <label>Odometer awal (km)</label>
            <input type="number" name="odometer_awal" class="form-input" inputmode="numeric" min="0"
                   value="{{ old('odometer_awal', $pengiriman->kendaraan?->odometer_terakhir) }}" required style="margin-bottom:0.75rem;">
            <button type="submit" class="btn-besar btn-hijau">Mulai Perjalanan</button>
        </form>
    </div>
@endif

<h3 style="margin:1rem 0 0.5rem;">Daftar Pesanan</h3>
@foreach($pengiriman->pesanans as $i => $ps)
    <div class="kartu">
        <h3>{{ $i + 1 }}. {{ $ps->nama_penerima }} <span class="badge {{ $ps->status === 'terkirim' ? 'active' : 'inactive' }}">{{ strtoupper(str_replace('_', ' ', $ps->status)) }}</span></h3>
        <div>{{ $ps->alamat_tujuan }}, {{ $ps->kota_tujuan }}</div>
        <div>Resi: {{ $ps->kode_resi }} · {{ number_format($ps->berat_kg, 1, ',', '.') }} kg · {{ $ps->jumlah_koli }} koli</div>
        @if($ps->deskripsi_barang)<div>Barang: {{ $ps->deskripsi_barang }}</div>@endif
        <div>Telepon: <strong>{{ $ps->noHpPenerimaUntukSopir() }}</strong></div>

        <div class="baris-aksi" style="margin-top:0.75rem;">
            <a class="btn-besar btn-abu" target="_blank" rel="noopener"
               href="https://www.google.com/maps/search/?api=1&query={{ urlencode($ps->alamat_tujuan . ', ' . $ps->kota_tujuan) }}">Buka Peta</a>
            @if(!str_starts_with($ps->noHpPenerimaUntukSopir(), '•') && $ps->noHpPenerimaUntukSopir() !== '-')
                <a class="btn-besar btn-abu" href="tel:{{ $ps->no_hp_penerima }}">Telepon</a>
            @endif
        </div>

        @if($pengiriman->status === 'berjalan' && $ps->status === 'dalam_perjalanan')
            <details class="aksi">
                <summary>Tandai Terkirim</summary>
                <form action="{{ route('portal.terkirim', [$pengiriman->id, $ps->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label>Nama penerima aktual</label>
                    <input type="text" name="nama_penerima" class="form-input" required style="margin-bottom:0.5rem;">
                    <label>Foto bukti (JPG/PNG, maks 2 MB)</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png" capture="environment" class="form-input" required style="margin-bottom:0.5rem;">
                    <label>Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-input" style="margin-bottom:0.75rem;">
                    <button type="submit" class="btn-besar btn-hijau">Simpan Terkirim</button>
                </form>
            </details>
            <details class="aksi">
                <summary>Gagal Kirim</summary>
                <form action="{{ route('portal.gagal', [$pengiriman->id, $ps->id]) }}" method="POST">
                    @csrf
                    <label>Alasan</label>
                    <select name="alasan" class="form-input" required style="margin-bottom:0.5rem;">
                        @foreach(['Penerima tidak ada', 'Alamat tidak ditemukan', 'Ditolak penerima', 'Barang rusak', 'Lainnya'] as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="catatan" class="form-input" placeholder="Keterangan tambahan" style="margin-bottom:0.75rem;">
                    <button type="submit" class="btn-besar btn-merah">Simpan Gagal Kirim</button>
                </form>
            </details>
        @endif
    </div>
@endforeach

@if($pengiriman->status === 'berjalan')
    <div class="kartu">
        <h3>Selesai Pengiriman</h3>
        <p style="color:var(--text-light); margin-bottom:0.5rem;">Semua pesanan harus sudah ditandai terkirim atau gagal.</p>
        <form action="{{ route('portal.selesai', $pengiriman->id) }}" method="POST">
            @csrf
            <label>Odometer akhir (km)</label>
            <input type="number" name="odometer_akhir" class="form-input" inputmode="numeric" min="{{ $pengiriman->odometer_awal }}" required style="margin-bottom:0.75rem;">
            <button type="submit" class="btn-besar btn-biru">Selesaikan Pengiriman</button>
        </form>
    </div>
@endif
@endsection
