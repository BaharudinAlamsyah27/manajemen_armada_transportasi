<?php

namespace App\Enums;

enum JabatanKaryawan: string
{
    case SOPIR = 'sopir';
    case KERNET = 'kernet';
    case MEKANIK = 'mekanik';
    case STAF_GUDANG = 'staf_gudang';
    case STAF_ADMIN = 'staf_admin';
}