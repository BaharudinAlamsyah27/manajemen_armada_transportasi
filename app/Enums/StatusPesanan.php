<?php

namespace App\Enums;

enum StatusPesanan: string
{
    case DRAF = 'draf';
    case MENUNGGU_JADWAL = 'menunggu_jadwal';
    case TERJADWAL = 'terjadwal';
    case DALAM_PERJALANAN = 'dalam_perjalanan';
    case TERKIRIM = 'terkirim';
    case GAGAL_KIRIM = 'gagal_kirim';
    case DIBATALKAN = 'dibatalkan';
}