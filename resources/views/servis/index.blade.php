@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Servis Armada & Stok Suku Cadang</h2>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Catat Servis Kendaraan</h3>
        <form action="{{ route('servis.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Kendaraan</label>
                <select name="kendaraan_id" class="form-input" required>
                    @foreach($kendaraans as $k)
                        <option value="{{ $k->id }}">{{ $k->plat_nomor }} ({{ strtoupper($k->status) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jenis Servis</label>
                <select name="jenis" class="form-input" required>
                    @foreach(['berkala', 'perbaikan', 'ganti_ban', 'kir', 'lainnya'] as $j)
                        <option value="{{ $j }}">{{ ucfirst(str_replace('_', ' ', $j)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Masuk & Odometer (km)</label>
                <div style="display:flex; gap:0.5rem;">
                    <input type="date" name="tanggal_masuk" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
                    <input type="number" name="odometer" class="form-input" placeholder="Odometer" value="1000" required>
                </div>
            </div>
            <div class="form-group">
                <label>Biaya Jasa (Rp)</label>
                <input type="number" name="biaya_jasa" class="form-input" value="150000" required>
            </div>
            <button type="submit" class="btn btn-sm">Catat Servis</button>
        </form>
    </div>

    <div class="table-container" style="padding:1.25rem;">
        <h3 style="margin-bottom:0.75rem;">Mutasi Stok Suku Cadang</h3>
        <form action="{{ route('servis.stok') }}" method="POST">
            @csrf
            @if(Auth::user()->hasRole('admin_pusat'))
            <div class="form-group">
                <label>Cabang</label>
                <select name="cabang_id" class="form-input" required>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group">
                <label>Suku Cadang</label>
                <select name="suku_cadang_id" class="form-input" required>
                    @foreach($sukuCadangs as $sc)
                        <option value="{{ $sc->id }}">{{ $sc->kode }} - {{ $sc->nama }} ({{ $sc->satuan }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tipe Mutasi & Jumlah</label>
                <div style="display:flex; gap:0.5rem;">
                    <select name="tipe" class="form-input" required>
                        <option value="masuk">Stok Masuk (+)</option>
                        <option value="keluar_servis">Keluar Servis (-)</option>
                    </select>
                    <input type="number" name="qty" class="form-input" value="5" min="1" required>
                </div>
            </div>
            <button type="submit" class="btn btn-sm">Simpan Mutasi Stok</button>
        </form>
    </div>
</div>

<div class="table-container" style="margin-bottom:1.5rem;">
    <h3 style="padding:1rem 1rem 0;">Riwayat Servis Kendaraan</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal Masuk</th>
                <th>Kendaraan</th>
                <th>Jenis</th>
                <th>Total Biaya</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servisList as $sv)
            <tr>
                <td>{{ $sv->tanggal_masuk?->format('d/m/Y') }}</td>
                <td>{{ $sv->kendaraan?->plat_nomor ?? '-' }}</td>
                <td>{{ strtoupper($sv->jenis) }}</td>
                <td>Rp {{ number_format($sv->total_biaya, 0, ',', '.') }}</td>
                <td><span class="badge {{ $sv->status === 'selesai' ? 'active' : 'inactive' }}">{{ strtoupper($sv->status) }}</span></td>
                <td>
                    @if($sv->status === 'dikerjakan')
                    <form action="{{ route('servis.selesai', $sv) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm">Tandai Selesai</button>
                    </form>
                    @else
                        Selesai ({{ $sv->tanggal_selesai?->format('d/m/Y') }})
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">Belum ada riwayat servis.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
