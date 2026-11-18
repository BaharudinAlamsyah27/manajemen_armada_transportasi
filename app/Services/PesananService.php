<?php

namespace App\Services;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\PesananStatusLog;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananService
{
    private const TRANSISI_VALID = [
        'draf' => ['menunggu_jadwal', 'dibatalkan'],
        'menunggu_jadwal' => ['terjadwal', 'dibatalkan'],
        'terjadwal' => ['menunggu_jadwal', 'dalam_perjalanan', 'dibatalkan'],
        'dalam_perjalanan' => ['terkirim', 'gagal_kirim'],
        'gagal_kirim' => ['menunggu_jadwal', 'dibatalkan'],
        'terkirim' => [],
        'dibatalkan' => [],
    ];

    public static function generateKodeResi(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $acak = '';
            for ($i = 0; $i < 6; $i++) {
                $acak .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $kode = 'FF' . date('ymd') . $acak;
        } while (Pesanan::withoutGlobalScopes()->where('kode_resi', $kode)->exists());

        return $kode;
    }

    public function createPesanan(array $data): Pesanan
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $cabangId = $data['cabang_id'] ?? ($user ? $user->cabang_id : null);
            $beratKg = (float) ($data['berat_kg'] ?? 0);
            $tarif = isset($data['tarif']) ? (float) $data['tarif'] : max(25000, $beratKg * 5000);
            $statusAwal = $data['status'] ?? StatusPesanan::MENUNGGU_JADWAL->value;

            $pesanan = Pesanan::create([
                'cabang_id' => $cabangId,
                'pelanggan_id' => $data['pelanggan_id'] ?? null,
                'kode_resi' => $data['kode_resi'] ?? self::generateKodeResi(),
                'tanggal_pesan' => $data['tanggal_pesan'] ?? now()->toDateString(),
                'nama_pengirim' => $data['nama_pengirim'] ?? null,
                'no_hp_pengirim' => $data['no_hp_pengirim'] ?? null,
                'alamat_asal' => $data['alamat_asal'] ?? null,
                'kota_asal' => $data['kota_asal'] ?? null,
                'nama_penerima' => $data['nama_penerima'],
                'no_hp_penerima' => $data['no_hp_penerima'] ?? null,
                'alamat_tujuan' => $data['alamat_tujuan'],
                'kota_tujuan' => $data['kota_tujuan'],
                'deskripsi_barang' => $data['deskripsi_barang'] ?? null,
                'berat_kg' => $beratKg,
                'jumlah_koli' => $data['jumlah_koli'] ?? 1,
                'tarif' => $tarif,
                'status' => $statusAwal,
                'dibuat_oleh' => $user ? $user->id : null,
            ]);

            PesananStatusLog::create([
                'pesanan_id' => $pesanan->id,
                'status_lama' => null,
                'status_baru' => $statusAwal,
                'catatan' => 'Pesanan baru dibuat.',
                'user_id' => $user ? $user->id : null,
                'waktu' => now(),
                'sumber' => 'admin',
            ]);

            return $pesanan;
        });
    }

    /**
     * Transisi yang boleh dipicu manual dari halaman pesanan (admin).
     * terjadwal / dalam_perjalanan / terkirim hanya lewat alur pengiriman.
     */
    public const TRANSISI_MANUAL = [
        'draf' => ['menunggu_jadwal', 'dibatalkan'],
        'menunggu_jadwal' => ['dibatalkan'],
        'terjadwal' => [],
        'dalam_perjalanan' => [],
        'gagal_kirim' => ['menunggu_jadwal', 'dibatalkan'],
        'terkirim' => [],
        'dibatalkan' => [],
    ];

    public static function transisiManual(string $statusLama): array
    {
        return self::TRANSISI_MANUAL[$statusLama] ?? [];
    }

    public function updateStatus(Pesanan $pesanan, string $statusBaru, ?string $catatan = null, string $sumber = 'admin'): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $statusBaru, $catatan, $sumber) {
            $pesanan->refresh();
            $statusLama = $pesanan->status;
            $allowed = self::TRANSISI_VALID[$statusLama] ?? [];

            $user = Auth::user();
            // Koreksi status final hanya oleh admin_pusat dengan alasan (Pedoman 6.1).
            $isKoreksiPusat = in_array($statusLama, ['terkirim', 'dibatalkan'], true)
                && $user && $user->hasRole('admin_pusat') && !empty($catatan);

            if (!in_array($statusBaru, $allowed, true) && !$isKoreksiPusat) {
                throw new Exception("Perubahan status dari '{$statusLama}' ke '{$statusBaru}' tidak diizinkan.");
            }

            if (in_array($statusBaru, ['dibatalkan', 'gagal_kirim'], true) && empty($catatan)) {
                throw new Exception("Alasan/catatan wajib diisi untuk status '{$statusBaru}'.");
            }

            $updateData = ['status' => $statusBaru];
            if ($statusBaru === 'menunggu_jadwal') {
                $updateData['pengiriman_id'] = null;
            }

            $pesanan->update($updateData);

            PesananStatusLog::create([
                'pesanan_id' => $pesanan->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'catatan' => $catatan,
                'user_id' => $user ? $user->id : null,
                'waktu' => now(),
                'sumber' => $sumber,
            ]);

            return $pesanan;
        });
    }
}
