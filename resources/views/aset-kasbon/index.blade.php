@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Aset Tetap, Penyusutan, Hutang Pemasok & Kasbon</h2>
    <form action="{{ route('aset.penyusutan') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm">+ Jalankan Penyusutan Bulanan Otomatis</button>
    </form>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">
    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Daftarkan Aset Tetap Armada</h3>
        <form action="{{ route('aset.store') }}" method="POST">
            @csrf
            <div class="form-group"><label>Cabang</label><select name="cabang_id" class="form-input" required>@foreach($cabangs as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach</select></div>
            <div class="form-group"><label>Kendaraan (Opsional)</label><select name="kendaraan_id" class="form-input"><option value="">- Non Kendaraan -</option>@foreach($kendaraans as $k)<option value="{{ $k->id }}">{{ $k->plat_nomor }}</option>@endforeach</select></div>
            <div class="form-group"><label>Nama Aset</label><input type="text" name="nama" class="form-input" placeholder="Truk Box B 1001 AA" required></div>
            <div class="form-group"><label>Tanggal Perolehan</label><input type="date" name="tanggal_perolehan" class="form-input" value="{{ now()->subMonths(6)->format('Y-m-d') }}" required></div>
            <div class="form-group"><label>Harga Perolehan & Nilai Residu (Rp)</label>
                <div style="display:flex; gap:0.5rem;">
                    <input type="number" name="harga_perolehan" class="form-input" value="240000000" required>
                    <input type="number" name="nilai_residu" class="form-input" value="24000000" required>
                </div>
            </div>
            <div class="form-group"><label>Umur Ekonomis (Bulan)</label><input type="number" name="umur_bulan" class="form-input" value="60" required></div>
            <button type="submit" class="btn btn-sm">Simpan Aset</button>
        </form>
    </div>

    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Catat Kasbon Karyawan</h3>
        <form action="{{ route('kasbon.store') }}" method="POST">
            @csrf
            <div class="form-group"><label>Karyawan</label><select name="karyawan_id" class="form-input" required>@foreach($karyawans as $kr)<option value="{{ $kr->id }}">{{ $kr->nama }} ({{ $kr->kode }})</option>@endforeach</select></div>
            <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-input" value="{{ now()->format('Y-m-d') }}" required></div>
            <div class="form-group"><label>Jumlah Pinjaman (Rp)</label><input type="number" name="jumlah" class="form-input" value="500000" required></div>
            <div class="form-group"><label>Cicilan Potong Gaji / Bulan (Rp)</label><input type="number" name="cicilan_per_bulan" class="form-input" value="250000" required></div>
            <button type="submit" class="btn btn-sm">Cairkan Kasbon</button>
        </form>

        <hr style="margin:1.25rem 0; border-color:var(--border);">
        <h3 style="margin-bottom:0.75rem;">Catat Tagihan Pemasok (Hutang Usaha)</h3>
        <form action="{{ route('tagihan-pemasok.store') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:0.5rem;">
                <select name="cabang_id" class="form-input" required>@foreach($cabangs as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach</select>
                <select name="pemasok_id" class="form-input" required>@foreach($pemasoks as $pm)<option value="{{ $pm->id }}">{{ $pm->nama }}</option>@endforeach</select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-bottom:0.75rem;">
                <input type="date" name="tanggal" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                <input type="number" name="jumlah" class="form-input" placeholder="Nominal Rp" value="350000" required>
            </div>
            <button type="submit" class="btn btn-sm">Catat Hutang Pemasok</button>
        </form>
    </div>
</div>

<div class="table-container" style="margin-bottom:1.5rem;">
    <h3 style="padding:1rem 1rem 0;">Daftar Aset Tetap & Nilai Buku</h3>
    <table>
        <thead><tr><th>Nama Aset</th><th>Cabang</th><th>Harga Perolehan</th><th>Akumulasi Penyusutan</th><th>Nilai Buku</th><th>Umur</th></tr></thead>
        <tbody>
            @forelse($asets as $a)
            <tr>
                <td><strong>{{ $a->nama }}</strong></td>
                <td>{{ $a->cabang?->nama }}</td>
                <td>Rp {{ number_format($a->harga_perolehan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($a->akumulasi_penyusutan, 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format((float)$a->harga_perolehan - (float)$a->akumulasi_penyusutan, 0, ',', '.') }}</strong></td>
                <td>{{ $a->umur_bulan }} bln</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Belum ada aset tetap.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-container">
    <h3 style="padding:1rem 1rem 0;">Daftar Kasbon Karyawan</h3>
    <table>
        <thead><tr><th>Tanggal</th><th>Karyawan</th><th>Jumlah</th><th>Cicilan / Bln</th><th>Sisa</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($kasbons as $kb)
            <tr>
                <td>{{ $kb->tanggal?->format('d/m/Y') }}</td>
                <td><strong>{{ $kb->karyawan?->nama }}</strong></td>
                <td>Rp {{ number_format($kb->jumlah, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($kb->cicilan_per_bulan, 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format($kb->sisa, 0, ',', '.') }}</strong></td>
                <td><span class="badge active">{{ strtoupper($kb->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Belum ada kasbon karyawan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
