<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesanan extends Model
{
    use SoftDeletes, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'pelanggan_id',
        'kode_resi',
        'tanggal_pesan',
        'nama_pengirim',
        'no_hp_pengirim',
        'alamat_asal',
        'kota_asal',
        'nama_penerima',
        'no_hp_penerima',
        'alamat_tujuan',
        'kota_tujuan',
        'deskripsi_barang',
        'berat_kg',
        'jumlah_koli',
        'tarif',
        'status',
        'pengiriman_id',
        'token_publik',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pesan' => 'date',
            'no_hp_pengirim' => 'encrypted',
            'no_hp_penerima' => 'encrypted',
            'berat_kg' => 'decimal:2',
            'jumlah_koli' => 'integer',
            'tarif' => 'decimal:2',
        ];
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PesananStatusLog::class, 'pesanan_id')->orderBy('waktu', 'desc');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function buktiPengirimans(): HasMany
    {
        return $this->hasMany(BuktiPengiriman::class, 'pesanan_id');
    }

    /**
     * Samarkan nomor telepon, mis. 081234567890 -> ••••••7890.
     */
    public static function samarkan(?string $nomor): string
    {
        if (!$nomor) {
            return '-';
        }

        return str_repeat('•', 6) . substr($nomor, -4);
    }

    /**
     * Nomor penerima untuk portal sopir: penuh hanya bila pengirimannya
     * terjadwal/berjalan dan dijadwalkan hari ini (MODUL-SOPIR-GPS 4.1).
     */
    public function noHpPenerimaUntukSopir(): string
    {
        $p = $this->pengiriman;
        $aktifHariIni = $p
            && in_array($p->status, ['terjadwal', 'berjalan'], true)
            && $p->tanggal_rencana?->isToday();

        return $aktifHariIni ? ($this->no_hp_penerima ?? '-') : self::samarkan($this->no_hp_penerima);
    }
}
