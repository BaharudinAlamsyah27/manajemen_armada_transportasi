<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\NomorDokumen;
use Illuminate\Support\Facades\DB;

class NomorDokumenService
{
    public static function buat(?int $cabangId, string $jenis, ?string $periode = null): string
    {
        return DB::transaction(function () use ($cabangId, $jenis, $periode) {
            $kodeCabang = 'PST';
            if ($cabangId) {
                $cabang = Cabang::find($cabangId);
                if ($cabang) {
                    $kodeCabang = strtoupper($cabang->kode);
                }
            }

            $row = NomorDokumen::where('cabang_id', $cabangId)
                ->where('jenis', $jenis)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $row = NomorDokumen::create([
                    'cabang_id' => $cabangId,
                    'jenis' => $jenis,
                    'periode' => $periode,
                    'terakhir' => 1,
                ]);
            } else {
                $row->increment('terakhir');
            }

            $urut = str_pad((string) $row->terakhir, 4, '0', STR_PAD_LEFT);

            if ($periode) {
                return "{$jenis}-{$kodeCabang}-{$periode}-{$urut}";
            }

            return "{$jenis}-{$kodeCabang}-{$urut}";
        });
    }
}
