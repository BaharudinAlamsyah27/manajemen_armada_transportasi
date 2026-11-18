<?php

namespace App\Services;

use App\Models\MutasiSukuCadang;
use App\Models\StokSukuCadang;
use App\Models\SukuCadang;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StokSukuCadangService
{
    public function mutasi(int $cabangId, int $sukuCadangId, string $tipe, int $qty, ?float $hargaSatuan = null, ?string $keterangan = null, ?string $refType = null, ?int $refId = null): StokSukuCadang
    {
        return DB::transaction(function () use ($cabangId, $sukuCadangId, $tipe, $qty, $hargaSatuan, $keterangan, $refType, $refId) {
            $master = SukuCadang::findOrFail($sukuCadangId);
            $stok = StokSukuCadang::withoutGlobalScopes()
                ->where('cabang_id', $cabangId)
                ->where('suku_cadang_id', $sukuCadangId)
                ->lockForUpdate()
                ->first();

            if (!$stok) {
                $stok = StokSukuCadang::create([
                    'cabang_id' => $cabangId,
                    'suku_cadang_id' => $sukuCadangId,
                    'qty' => 0,
                ]);
            }

            $delta = $tipe === 'keluar_servis' ? -abs($qty) : $qty;
            $qtyBaru = $stok->qty + $delta;

            if ($qtyBaru < 0) {
                throw new Exception("Stok suku cadang '{$master->nama}' tidak mencukupi (tersedia: {$stok->qty}, dibutuhkan: " . abs($delta) . ").");
            }

            $stok->update(['qty' => $qtyBaru]);

            MutasiSukuCadang::create([
                'cabang_id' => $cabangId,
                'suku_cadang_id' => $sukuCadangId,
                'tipe' => $tipe,
                'qty' => $delta,
                'harga_satuan' => $hargaSatuan ?? (float) $master->harga_standar,
                'referensi_type' => $refType,
                'referensi_id' => $refId,
                'keterangan' => $keterangan,
                'user_id' => Auth::id(),
                'tanggal' => now(),
            ]);

            return $stok;
        });
    }
}
