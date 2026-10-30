<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\PesananStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PesananService
{
    /**
     * Membuat Pesanan/Resi baru dengan perhitungan biaya dan berat volumetrik.
     */
    public function createPesanan(array \$data)
    {
        return DB::transaction(function () use (\$data) {
            // 1. Hitung Berat Volumetrik (P x L x T / 4000) - standar logistik darat
            \$beratAktual = \$data['berat_kg'] ?? 0;
            \$volume = (\$data['panjang_cm'] ?? 0) * (\$data['lebar_cm'] ?? 0) * (\$data['tinggi_cm'] ?? 0);
            \$beratVolumetrik = \$volume / 4000;
            
            // Pilih berat terbesar sebagai dasar tarif (Chargeable Weight)
            \$chargeableWeight = max(\$beratAktual, \$beratVolumetrik);
            
            // 2. Kalkulasi Biaya (Misal tarif dasar Rp 5.000/kg)
            // Dalam dunia nyata, tarif bergantung dari origin-destination, ini versi disederhanakan
            \$biayaKirim = \$chargeableWeight * 5000; 

            // 3. Generate Nomor Resi (Format: AW-[KODE_CABANG]-[Ymd]-[RANDOM])
            \$user = Auth::user();
            \$kodeCabang = \$user->cabang ? \$user->cabang->kode : 'PST';
            \$nomorResi = 'AW-' . \$kodeCabang . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // 4. Simpan Data Pesanan
            \$pesanan = Pesanan::create([
                'cabang_id' => \$user->cabang_id,
                'nomor_resi' => \$nomorResi,
                'pengirim_nama' => \$data['pengirim_nama'],
                'pengirim_no_hp' => \$data['pengirim_no_hp'],
                'pengirim_alamat' => \$data['pengirim_alamat'],
                'penerima_nama' => \$data['penerima_nama'],
                'penerima_no_hp' => \$data['penerima_no_hp'],
                'penerima_alamat' => \$data['penerima_alamat'],
                'kota_tujuan' => \$data['kota_tujuan'],
                'berat_kg' => \$beratAktual,
                'panjang_cm' => \$data['panjang_cm'],
                'lebar_cm' => \$data['lebar_cm'],
                'tinggi_cm' => \$data['tinggi_cm'],
                'status' => 'menunggu_jadwal', // Status awal standar logistik
                'total_biaya' => \$biayaKirim,
            ]);

            // 5. Catat ke Log Status (Audit Trail)
            PesananStatusLog::create([
                'pesanan_id' => \$pesanan->id,
                'status' => 'menunggu_jadwal',
                'keterangan' => 'Pesanan baru diterima di cabang.',
                'user_id' => \$user->id,
            ]);

            return \$pesanan;
        });
    }

    /**
     * Update status pesanan beserta tracking history.
     */
    public function updateStatus(Pesanan \$pesanan, string \$statusBaru, string \$keterangan = null)
    {
        return DB::transaction(function () use (\$pesanan, \$statusBaru, \$keterangan) {
            \$pesanan->update(['status' => \$statusBaru]);
            
            PesananStatusLog::create([
                'pesanan_id' => \$pesanan->id,
                'status' => \$statusBaru,
                'keterangan' => \$keterangan,
                'user_id' => Auth::id(),
            ]);

            return \$pesanan;
        });
    }
}
