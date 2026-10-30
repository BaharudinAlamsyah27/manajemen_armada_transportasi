<?php

namespace App\Enums;

enum StatusPengiriman: string
{
    case TERJADWAL = 'terjadwal';
    case BERJALAN = 'berjalan';
    case SELESAI = 'selesai';
    case BATAL = 'batal';
}