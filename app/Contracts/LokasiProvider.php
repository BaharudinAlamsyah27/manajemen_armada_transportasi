<?php

namespace App\Contracts;

use App\Models\LokasiPing;
use App\Models\SesiPelacakan;

interface LokasiProvider
{
    public function catat(SesiPelacakan $sesi, array $data): LokasiPing;
}
