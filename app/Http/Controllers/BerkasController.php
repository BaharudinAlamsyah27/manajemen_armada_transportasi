<?php

namespace App\Http\Controllers;

use App\Models\BbmLog;
use App\Models\BiayaOperasional;
use App\Models\BuktiPengiriman;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Akses berkas unggahan di disk privat lewat rute terotorisasi (Pedoman 7).
 */
class BerkasController extends Controller
{
    public function bukti(Request $request, BuktiPengiriman $bukti)
    {
        $user = $request->user();

        // Pesanan memakai global scope cabang: admin cabang lain otomatis 404.
        $pesanan = Pesanan::find($bukti->pesanan_id);
        abort_unless($pesanan, 404);

        if ($user->hasRole('sopir')) {
            abort_unless($bukti->pengiriman && (int) $bukti->pengiriman->sopir_id === (int) $user->karyawan_id, 404);
        }

        return $this->kirim($bukti->path);
    }

    public function bbm(BbmLog $bbmLog)
    {
        return $this->kirim($bbmLog->bukti_path);
    }

    public function biaya(BiayaOperasional $biaya)
    {
        return $this->kirim($biaya->bukti_path);
    }

    private function kirim(?string $path)
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
