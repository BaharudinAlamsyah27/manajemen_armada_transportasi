<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan {{ $pengiriman->kode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0F172A; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #94A3B8; padding: 6px 8px; text-align: left; }
        th { background: #F1F5F9; }
        .ttd { width: 100%; margin-top: 35px; border: none; }
        .ttd td { border: none; text-align: center; width: 33%; }
    </style>
</head>
<body>
    <h2>SURAT JALAN / MANIFEST PENGIRIMAN: {{ $pengiriman->kode }}</h2>
    <p>
        Cabang: <strong>{{ $pengiriman->cabang?->nama }}</strong> |
        Tanggal: <strong>{{ $pengiriman->tanggal_rencana?->format('d/m/Y') }} (Rit #{{ $pengiriman->rit_ke }})</strong><br>
        Kendaraan: <strong>{{ $pengiriman->kendaraan?->plat_nomor }} ({{ strtoupper($pengiriman->kendaraan?->jenis ?? '') }})</strong> |
        Sopir: <strong>{{ $pengiriman->sopir?->nama }}</strong>
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Resi</th>
                <th>Penerima</th>
                <th>Alamat & Kota Tujuan</th>
                <th>Koli / Berat</th>
                <th>Tanda Terima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengiriman->pesanans as $i => $ps)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $ps->kode_resi }}</strong></td>
                <td>{{ $ps->nama_penerima }}</td>
                <td>{{ $ps->alamat_tujuan }}, {{ $ps->kota_tujuan }}</td>
                <td>{{ $ps->jumlah_koli }} koli ({{ number_format($ps->berat_kg, 1, ',', '.') }} kg)</td>
                <td>......................</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>Admin Operasional<br><br><br><br>( ........................ )</td>
            <td>Sopir Bertugas<br><br><br><br>( {{ $pengiriman->sopir?->nama }} )</td>
            <td>Petugas Gudang / Keamanan<br><br><br><br>( ........................ )</td>
        </tr>
    </table>
</body>
</html>
