<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Resi {{ $pesanan->kode_resi }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0F172A; }
        .box { border: 2px solid #0F172A; padding: 14px; border-radius: 6px; }
        .header { border-bottom: 2px solid #0F172A; padding-bottom: 10px; margin-bottom: 12px; }
        .resi { font-size: 20px; font-weight: bold; letter-spacing: 1px; color: #1D4ED8; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #CBD5E1; padding: 6px 8px; text-align: left; }
        th { background: #F1F5F9; width: 32%; }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">
            <div style="font-size:16px; font-weight:bold;">FLEETFLOW LOGISTICS — BUKTI RESI PENGIRIMAN</div>
            <div>Cabang Asal: {{ $pesanan->cabang?->nama ?? 'Pusat' }} | Tanggal: {{ $pesanan->tanggal_pesan?->format('d/m/Y') }}</div>
            <div class="resi">{{ $pesanan->kode_resi }}</div>
        </div>
        <table>
            <tr><th>Pengirim</th><td><strong>{{ $pesanan->nama_pengirim ?? '-' }}</strong> ({{ $pesanan->kota_asal ?? '-' }})<br>{{ $pesanan->alamat_asal ?? '-' }}</td></tr>
            <tr><th>Penerima</th><td><strong>{{ $pesanan->nama_penerima }}</strong> ({{ $pesanan->kota_tujuan }})<br>{{ $pesanan->alamat_tujuan }}</td></tr>
            <tr><th>Deskripsi Barang</th><td>{{ $pesanan->deskripsi_barang ?? 'Kargo Umum' }}</td></tr>
            <tr><th>Berat & Koli</th><td>{{ number_format($pesanan->berat_kg, 1, ',', '.') }} kg / {{ $pesanan->jumlah_koli }} Koli</td></tr>
            <tr><th>Biaya Pengiriman</th><td><strong>Rp {{ number_format($pesanan->tarif, 0, ',', '.') }}</strong></td></tr>
            <tr><th>Lacak Online</th><td>{{ $urlLacak }}</td></tr>
        </table>
    </div>
</body>
</html>
