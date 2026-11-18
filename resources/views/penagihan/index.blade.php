@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Penagihan, Pelanggan, Tarif & Piutang</h2>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Tambah Pelanggan</h3>
        <form action="{{ route('penagihan.pelanggan') }}" method="POST">
            @csrf
            <div class="form-group"><label>Nama Pelanggan</label><input type="text" name="nama" class="form-input" required></div>
            <div class="form-group"><label>Termin (Hari)</label><input type="number" name="termin_hari" class="form-input" value="14" required></div>
            <button type="submit" class="btn btn-sm" style="width:auto;">Simpan Pelanggan</button>
        </form>
    </div>

    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Terbitkan Invoice dari Pesanan Terkirim</h3>
        <form action="{{ route('penagihan.invoice') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Pelanggan</label>
                <select name="pelanggan_id" class="form-input" required>
                    @foreach($pelanggans as $plg)<option value="{{ $plg->id }}">{{ $plg->kode }} - {{ $plg->nama }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Pesanan Terkirim</label>
                <select name="pesanan_ids[]" class="form-input" multiple required>
                    @foreach($pesanansSiap as $ps)<option value="{{ $ps->id }}">{{ $ps->kode_resi }} — Rp {{ number_format($ps->tarif, 0, ',', '.') }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>PPN (%)</label><input type="number" step="0.1" name="ppn_persen" class="form-input" value="0"></div>
            <button type="submit" class="btn btn-sm" style="width:auto;">Terbitkan Invoice</button>
        </form>
    </div>
</div>

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Daftar Invoice & Pembayaran</h3>
    <table>
        <thead><tr><th>No Invoice</th><th>Pelanggan</th><th>Total</th><th>Terbayar</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($invoices as $inv)
            <tr>
                <td><strong>{{ $inv->no_invoice }}</strong></td>
                <td>{{ $inv->pelanggan?->nama }}</td>
                <td>Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($inv->terbayar, 0, ',', '.') }}</td>
                <td><span class="badge active">{{ strtoupper($inv->status) }}</span></td>
                <td>
                    <a href="{{ route('penagihan.invoice.pdf', $inv) }}" style="color:var(--primary); margin-right:0.5rem;">PDF</a>
                    @if($inv->sisa() > 0)
                    <form action="{{ route('penagihan.bayar', $inv) }}" method="POST" style="display:inline-flex; gap:0.25rem; align-items:center;">
                        @csrf
                        <select name="akun_kas_id" style="padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                            @foreach($akunKas as $ak)<option value="{{ $ak->id }}">{{ $ak->nama }}</option>@endforeach
                        </select>
                        <input type="number" name="jumlah" value="{{ $inv->sisa() }}" max="{{ $inv->sisa() }}" step="0.01" style="width:110px; padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                        <button type="submit" class="btn btn-sm" style="width:auto; background:#16A34A;">Bayar</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Belum ada invoice.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
