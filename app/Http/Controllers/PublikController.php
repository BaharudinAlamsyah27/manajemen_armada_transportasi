<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Kendaraan;
use App\Models\Pesanan;
use App\Models\Tarif;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function index(Request $request)
    {
        $cabangs = Cabang::where('aktif', true)->orderBy('kode')->get();
        $tarifs = Tarif::where('aktif', true)->orderBy('kota_asal')->orderBy('kota_tujuan')->get();

        $ringkasan = [
            'total_cabang' => $cabangs->count(),
            'total_armada' => Kendaraan::withoutGlobalScopes()->where('status', '!=', 'nonaktif')->count(),
            'pesanan_terkirim' => Pesanan::withoutGlobalScopes()->where('status', 'terkirim')->count(),
        ];

        // Cek Resi langsung dari halaman depan
        $hasilResi = null;
        $kodeResi = trim((string) $request->query('resi', ''));
        if ($kodeResi !== '') {
            $hasilResi = Pesanan::withoutGlobalScopes()
                ->with('statusLogs')
                ->where('kode_resi', strtoupper($kodeResi))
                ->first();
        }

        // Kalkulator Cek Ongkir
        $hasilOngkir = null;
        if ($request->filled(['asal', 'tujuan', 'berat'])) {
            $asal = $request->query('asal');
            $tujuan = $request->query('tujuan');
            $berat = max(1, (float) $request->query('berat', 1));

            $tarif = Tarif::where('aktif', true)
                ->whereRaw('LOWER(kota_asal) = ?', [strtolower($asal)])
                ->whereRaw('LOWER(kota_tujuan) = ?', [strtolower($tujuan)])
                ->first();

            $hargaPerKg = $tarif ? (float) $tarif->harga_per_kg : 5000.0;
            $minimum = $tarif ? (float) $tarif->tarif_minimum : 25000.0;
            $estimasi = max($minimum, $berat * $hargaPerKg);

            $hasilOngkir = [
                'asal' => ucfirst($asal),
                'tujuan' => ucfirst($tujuan),
                'berat' => $berat,
                'harga_per_kg' => $hargaPerKg,
                'minimum' => $minimum,
                'total' => $estimasi,
            ];
        }

        return view('publik.beranda', compact('cabangs', 'tarifs', 'ringkasan', 'kodeResi', 'hasilResi', 'hasilOngkir'));
    }
}
