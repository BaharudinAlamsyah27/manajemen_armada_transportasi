<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Resi {{ $kode_resi }} - FleetFlow</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="background:#F8FAFC; padding:2rem;">
    <div style="max-width:650px; margin:0 auto; background:#fff; padding:2rem; border-radius:0.75rem; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h2>Pelacakan Resi FleetFlow</h2>
        <p style="color:#64748B; margin-bottom:1.5rem;">Kode Resi: <strong>{{ strtoupper($kode_resi) }}</strong></p>

        @if(!$pesanan)
            <div style="padding:1rem; background:#FEF2F2; color:#991B1B; border-radius:0.5rem;">
                Data resi tidak ditemukan atau belum terdaftar.
            </div>
        @else
            <div style="margin-bottom:1.5rem; padding:1rem; background:#F1F5F9; border-radius:0.5rem;">
                <div>Kota Asal: <strong>{{ $pesanan->kota_asal ?? '-' }}</strong> &rarr; Kota Tujuan: <strong>{{ $pesanan->kota_tujuan }}</strong></div>
                <div style="margin-top:0.5rem;">Status Saat Ini: <span class="badge active">{{ strtoupper(str_replace('_', ' ', $pesanan->status)) }}</span></div>
            </div>
            <h3>Timeline Perjalanan</h3>
            <table style="width:100%; margin-top:0.75rem;">
                <thead>
                    <tr>
                        <th style="text-align:left;">Waktu</th>
                        <th style="text-align:left;">Status</th>
                        <th style="text-align:left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesanan->statusLogs as $log)
                    <tr>
                        <td>{{ $log->waktu?->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ strtoupper(str_replace('_', ' ', $log->status_baru)) }}</strong></td>
                        <td>{{ $log->catatan ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
