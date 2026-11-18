<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan FleetFlow</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1E293B; }
        h1, h2 { margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #CBD5E1; padding: 6px 8px; text-align: left; }
        th { background: #F1F5F9; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan FleetFlow</h1>
    <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }} WIB (Mata Uang: IDR — DRAF BILA PERIODE MASIH TERBUKA)</p>

    <h2>1. Laba Rugi</h2>
    <table>
        <tr><td>Pendapatan Jasa</td><td class="right">Rp {{ number_format($labaRugi['pendapatan'], 0, ',', '.') }}</td></tr>
        <tr><td>Beban Pokok Jasa</td><td class="right">Rp {{ number_format($labaRugi['beban_pokok'], 0, ',', '.') }}</td></tr>
        <tr><th>Laba Kotor</th><th class="right">Rp {{ number_format($labaRugi['laba_kotor'], 0, ',', '.') }}</th></tr>
        <tr><td>Beban Usaha</td><td class="right">Rp {{ number_format($labaRugi['beban_usaha'], 0, ',', '.') }}</td></tr>
        <tr><th>Laba Bersih</th><th class="right">Rp {{ number_format($labaRugi['laba_bersih'], 0, ',', '.') }}</th></tr>
    </table>

    <h2>2. Neraca (Posisi Keuangan)</h2>
    <table>
        <tr><td>Total Aset</td><td class="right">Rp {{ number_format($neraca['aset'], 0, ',', '.') }}</td></tr>
        <tr><td>Total Liabilitas</td><td class="right">Rp {{ number_format($neraca['liabilitas'], 0, ',', '.') }}</td></tr>
        <tr><td>Total Ekuitas + Laba Berjalan</td><td class="right">Rp {{ number_format($neraca['ekuitas'] + $neraca['laba_berjalan'], 0, ',', '.') }}</td></tr>
        <tr><th>Status Keseimbangan</th><th class="right">{{ $neraca['seimbang'] ? 'SEIMBANG' : 'SELISIH' }}</th></tr>
    </table>
</body>
</html>
