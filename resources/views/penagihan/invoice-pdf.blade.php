<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->no_invoice }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1E293B; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #CBD5E1; padding: 6px 8px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>INVOICE: {{ $invoice->no_invoice }}</h2>
    <p>Pelanggan: <strong>{{ $invoice->pelanggan?->nama }}</strong><br>
    Tanggal: {{ $invoice->tanggal?->format('d/m/Y') }} | Jatuh Tempo: {{ $invoice->jatuh_tempo?->format('d/m/Y') }}</p>
    <table>
        <thead><tr><th>Deskripsi</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
            @foreach($invoice->items as $it)
            <tr><td>{{ $it->deskripsi }}</td><td class="right">Rp {{ number_format($it->jumlah, 0, ',', '.') }}</td></tr>
            @endforeach
            <tr><th>Subtotal</th><th class="right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</th></tr>
            <tr><td>PPN ({{ $invoice->ppn_persen }}%)</td><td class="right">Rp {{ number_format($invoice->ppn_nominal, 0, ',', '.') }}</td></tr>
            <tr><th>TOTAL</th><th class="right">Rp {{ number_format($invoice->total, 0, ',', '.') }}</th></tr>
        </tbody>
    </table>
</body>
</html>
