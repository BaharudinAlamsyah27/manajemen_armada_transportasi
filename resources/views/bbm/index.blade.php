@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Pencatatan BBM Armada</h2>
</div>

<div class="table-container" style="padding:1.25rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Catat Pengisian BBM Baru</h3>
    <form action="{{ route('bbm.store') }}" method="POST" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:0.75rem; align-items:end;">
        @csrf
        <div>
            <label>Kendaraan</label>
            <select name="kendaraan_id" class="form-input" required>
                @foreach($kendaraans as $k)
                    <option value="{{ $k->id }}">{{ $k->plat_nomor }} ({{ $k->odometer_terakhir }} km)</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
        </div>
        <div>
            <label>Liter</label>
            <input type="number" step="0.01" name="liter" class="form-input" placeholder="25.00" required>
        </div>
        <div>
            <label>Harga / Liter (Rp)</label>
            <input type="number" name="harga_per_liter" class="form-input" value="10000" required>
        </div>
        <div>
            <label>Odometer (km)</label>
            <input type="number" name="odometer" class="form-input" required>
        </div>
        <div>
            <button type="submit" class="btn">Simpan BBM</button>
        </div>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kendaraan</th>
                <th>Liter</th>
                <th>Harga/L</th>
                <th>Total (Server)</th>
                <th>Odometer</th>
                <th>Status Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bbmLogs as $log)
            <tr>
                <td>{{ $log->tanggal?->format('d/m/Y') }}</td>
                <td>{{ $log->kendaraan?->plat_nomor ?? '-' }}</td>
                <td>{{ number_format($log->liter, 2, ',', '.') }} L</td>
                <td>Rp {{ number_format($log->harga_per_liter, 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format($log->total, 0, ',', '.') }}</strong></td>
                <td>{{ number_format($log->odometer, 0, ',', '.') }} km</td>
                <td><span class="badge active">{{ strtoupper($log->status_verifikasi) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">Belum ada catatan pengisian BBM.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
