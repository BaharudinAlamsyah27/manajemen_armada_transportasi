<?php

namespace App\Enums;

enum StatusKendaraan: string
{
    case TERSEDIA = 'tersedia';
    case BERTUGAS = 'bertugas';
    case SERVIS = 'servis';
    case NONAKTIF = 'nonaktif';
}