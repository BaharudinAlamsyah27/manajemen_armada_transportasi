@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Akuntansi, Jurnal & Laporan Keuangan</h2>
    <a href="{{ route('keuangan.pdf') }}" class="btn btn-sm" style="width:auto;">Unduh Paket PDF</a>
</div>

<div class="grid-cards" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <h3>Laba Bersih (Akrual)</h3>
        <div class="value" style="font-size:1.3rem;">Rp {{ number_format($labaRugi['laba_bersih'], 0, ',', '.') }}</div>
        <small>Pendapatan: Rp {{ number_format($labaRugi['pendapatan'], 0, ',', '.') }}</small>
    </div>
    <div class="stat-card">
        <h3>Posisi Neraca</h3>
        <div class="value" style="font-size:1.3rem;">{{ $neraca['seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}</div>
        <small>Aset: Rp {{ number_format($neraca['aset'], 0, ',', '.') }} | Pasiva: Rp {{ number_format($neraca['total_pasiva'], 0, ',', '.') }}</small>
    </div>
    <div class="stat-card">
        <h3>Rekonsiliasi Subledger</h3>
        <div class="value" style="font-size:1.1rem;">Piutang: Rp {{ number_format($rekonsiliasi['piutang_subledger'], 0, ',', '.') }}</div>
        <small>Akun 1-1300: Rp {{ number_format($rekonsiliasi['piutang_akun'], 0, ',', '.') }}</small>
    </div>
    <div class="stat-card">
        <h3>Kas & Bank</h3>
        <div class="value" style="font-size:1.3rem;">Rp {{ number_format($rekonsiliasi['kas_bank_total'], 0, ',', '.') }}</div>
        <small>Periode Aktif: {{ $rekonsiliasi['periode_aktif']?->bulan }}/{{ $rekonsiliasi['periode_aktif']?->tahun }} ({{ strtoupper($rekonsiliasi['periode_aktif']?->status ?? '-') }})</small>
    </div>
</div>

<div class="table-container" style="padding:1.25rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Buat Jurnal Manual (Draf — Maker-Checker)</h3>
    <form action="{{ route('keuangan.jurnal.store') }}" method="POST" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:0.75rem; align-items:end;">
        @csrf
        <div><label>Tanggal</label><input type="date" name="tanggal" class="form-input" value="{{ now()->format('Y-m-d') }}" required></div>
        <div><label>Akun Debit</label><select name="akun_debit" class="form-input" required>@foreach($akuns as $a)<option value="{{ $a->id }}">{{ $a->kode }} - {{ $a->nama }}</option>@endforeach</select></div>
        <div><label>Akun Kredit</label><select name="akun_kredit" class="form-input" required>@foreach($akuns as $a)<option value="{{ $a->id }}">{{ $a->kode }} - {{ $a->nama }}</option>@endforeach</select></div>
        <div><label>Nominal (Rp)</label><input type="number" name="nominal" class="form-input" min="1" required></div>
        <div><label>Keterangan</label><input type="text" name="keterangan" class="form-input" required></div>
        <div><button type="submit" class="btn btn-sm">Simpan Draf</button></div>
    </form>
</div>

<div class="table-container" style="margin-bottom:1.5rem;">
    <h3 style="padding:1rem 1rem 0;">Jurnal Umum Terbaru</h3>
    <table>
        <thead><tr><th>No Jurnal</th><th>Tanggal</th><th>Keterangan</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($jurnals as $j)
            <tr>
                <td><strong>{{ $j->no_jurnal }}</strong></td>
                <td>{{ $j->tanggal?->format('d/m/Y') }}</td>
                <td>{{ $j->keterangan }}</td>
                <td>{{ strtoupper($j->jenis) }}</td>
                <td><span class="badge {{ $j->status === 'posted' ? 'active' : 'inactive' }}">{{ strtoupper($j->status) }}</span></td>
                <td>
                    @if($j->status === 'draf')
                        <form action="{{ route('keuangan.jurnal.posting', $j) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="width:auto;">Posting</button>
                        </form>
                    @elseif($j->status === 'posted' && $j->jenis !== 'balik')
                        <form action="{{ route('keuangan.jurnal.balik', $j) }}" method="POST" style="display:inline-flex; gap:0.25rem;">
                            @csrf
                            <input type="text" name="alasan" placeholder="Alasan balik" required style="width:120px; padding:0.25rem; border:1px solid #CBD5E1; border-radius:0.25rem;">
                            <button type="submit" class="btn btn-sm" style="width:auto; background:#DC2626;">Balik</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Belum ada jurnal tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Neraca Saldo (Posted)</h3>
    <table>
        <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th>Debit</th><th>Kredit</th><th>Saldo</th></tr></thead>
        <tbody>
            @foreach($neracaSaldo['rows'] as $r)
                @if($r['debit'] > 0 || $r['kredit'] > 0)
                <tr>
                    <td>{{ $r['akun']->kode }}</td>
                    <td>{{ $r['akun']->nama }}</td>
                    <td>{{ strtoupper($r['akun']->tipe) }}</td>
                    <td>Rp {{ number_format($r['debit'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($r['kredit'], 0, ',', '.') }}</td>
                    <td><strong>Rp {{ number_format($r['saldo'], 0, ',', '.') }}</strong></td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
