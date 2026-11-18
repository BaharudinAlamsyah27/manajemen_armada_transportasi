@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Verifikasi Input Lapangan (Sopir)</h2>
</div>

<div class="table-container" style="margin-bottom: 1.5rem;">
    <h3 style="padding: 1rem 1rem 0;">Antrean Catatan BBM ({{ $bbmLogs->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kendaraan / Sopir</th>
                <th>Liter & Harga</th>
                <th>Total</th>
                <th>Odometer</th>
                <th>Bukti</th>
                <th>Aksi Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bbmLogs as $log)
            <tr>
                <td>{{ $log->tanggal?->format('d/m/Y') }}</td>
                <td><strong>{{ $log->kendaraan?->plat_nomor }}</strong><br><small>{{ $log->sopir?->nama ?? '-' }}</small></td>
                <td>{{ number_format($log->liter, 2, ',', '.') }} L @ Rp {{ number_format($log->harga_per_liter, 0, ',', '.') }}</td>
                <td><strong>Rp {{ number_format($log->total, 0, ',', '.') }}</strong></td>
                <td>{{ number_format($log->odometer, 0, ',', '.') }} km</td>
                <td>
                    @if($log->bukti_path)
                        <a href="{{ route('berkas.bbm', $log) }}" target="_blank" style="color:var(--primary);">Lihat Foto</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    <form action="{{ route('verifikasi.bbm', $log) }}" method="POST" style="display:flex; gap:0.4rem; flex-wrap:wrap; align-items:center;">
                        @csrf
                        <input type="text" name="alasan_tolak" class="form-input" placeholder="Alasan jika tolak..." style="max-width:160px; padding:0.35rem;">
                        <button type="submit" name="keputusan" value="setujui" class="btn btn-sm" style="width:auto; background:#16A34A;">Setujui</button>
                        <button type="submit" name="keputusan" value="tolak" class="btn btn-sm" style="width:auto; background:#DC2626;">Tolak</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">Tidak ada antrean verifikasi BBM.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-container">
    <h3 style="padding: 1rem 1rem 0;">Antrean Biaya Operasional Jalan ({{ $biayas->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kendaraan</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Bukti</th>
                <th>Aksi Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($biayas as $b)
            <tr>
                <td>{{ $b->tanggal?->format('d/m/Y') }}</td>
                <td><strong>{{ $b->kendaraan?->plat_nomor ?? '-' }}</strong></td>
                <td>{{ strtoupper($b->jenis) }}</td>
                <td><strong>Rp {{ number_format($b->jumlah, 0, ',', '.') }}</strong></td>
                <td>{{ $b->keterangan ?? '-' }}</td>
                <td>
                    @if($b->bukti_path)
                        <a href="{{ route('berkas.biaya', $b) }}" target="_blank" style="color:var(--primary);">Lihat Foto</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    <form action="{{ route('verifikasi.biaya', $b) }}" method="POST" style="display:flex; gap:0.4rem; flex-wrap:wrap; align-items:center;">
                        @csrf
                        <input type="text" name="alasan_tolak" class="form-input" placeholder="Alasan jika tolak..." style="max-width:160px; padding:0.35rem;">
                        <button type="submit" name="keputusan" value="setujui" class="btn btn-sm" style="width:auto; background:#16A34A;">Setujui</button>
                        <button type="submit" name="keputusan" value="tolak" class="btn btn-sm" style="width:auto; background:#DC2626;">Tolak</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">Tidak ada antrean verifikasi biaya jalan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
