<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\PembayaranAlokasi;
use App\Models\Pesanan;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenagihanService
{
    public function buatInvoice(int $pelangganId, array $pesananIds, ?float $ppnPersen = null, ?string $tanggal = null): Invoice
    {
        return DB::transaction(function () use ($pelangganId, $pesananIds, $ppnPersen, $tanggal) {
            $pelanggan = Pelanggan::findOrFail($pelangganId);
            $pesanans = Pesanan::whereIn('id', $pesananIds)
                ->where('status', 'terkirim')
                ->lockForUpdate()
                ->get();

            if ($pesanans->isEmpty() || $pesanans->count() !== count($pesananIds)) {
                throw new Exception('Pesanan tidak valid atau belum berstatus terkirim.');
            }

            // Pastikan satu pesanan tidak masuk dua invoice aktif.
            $sudahDitagih = InvoiceItem::whereIn('pesanan_id', $pesananIds)
                ->whereHas('invoice', fn ($q) => $q->where('status', '!=', 'batal'))
                ->exists();
            if ($sudahDitagih) {
                throw new Exception('Salah satu pesanan sudah masuk ke invoice aktif lain.');
            }

            $cabangId = $pesanans->first()->cabang_id;
            $tgl = $tanggal ? Carbon::parse($tanggal) : now();
            $jatuhTempo = $tgl->copy()->addDays($pelanggan->termin_hari);
            $subtotal = (float) $pesanans->sum('tarif');
            $ppnRate = $ppnPersen ?? (float) Setting::getValue('ppn_persen', 0);
            $ppnNominal = round($subtotal * ($ppnRate / 100), 2);
            $total = round($subtotal + $ppnNominal, 2);

            $invoice = Invoice::create([
                'cabang_id' => $cabangId,
                'no_invoice' => NomorDokumenService::buat($cabangId, 'INV', $tgl->format('Ym')),
                'pelanggan_id' => $pelanggan->id,
                'tanggal' => $tgl->toDateString(),
                'jatuh_tempo' => $jatuhTempo->toDateString(),
                'subtotal' => $subtotal,
                'ppn_persen' => $ppnRate,
                'ppn_nominal' => $ppnNominal,
                'total' => $total,
                'terbayar' => 0,
                'status' => 'terbit',
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($pesanans as $ps) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'pesanan_id' => $ps->id,
                    'deskripsi' => "Jasa Angkut {$ps->kode_resi} ({$ps->kota_asal} - {$ps->kota_tujuan})",
                    'qty' => 1,
                    'harga' => (float) $ps->tarif,
                    'jumlah' => (float) $ps->tarif,
                ]);
            }

            (new JurnalOtomatisService())->catatInvoiceTerbit($invoice);

            return $invoice;
        });
    }

    public function catatPembayaran(int $pelangganId, int $akunKasId, array $alokasis, string $tanggal, string $metode = 'transfer', float $potonganPajak = 0): Pembayaran
    {
        return DB::transaction(function () use ($pelangganId, $akunKasId, $alokasis, $tanggal, $metode, $potonganPajak) {
            $pelanggan = Pelanggan::findOrFail($pelangganId);
            $totalAlokasi = 0.0;
            $cabangId = Auth::user()?->cabang_id;

            foreach ($alokasis as $invoiceId => $nominal) {
                $nominal = (float) $nominal;
                if ($nominal <= 0) {
                    continue;
                }
                $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                $cabangId = $cabangId ?? $invoice->cabang_id;
                if ((int) $invoice->pelanggan_id !== (int) $pelanggan->id) {
                    throw new Exception("Invoice {$invoice->no_invoice} bukan milik pelanggan ini.");
                }
                if ($nominal > $invoice->sisa()) {
                    throw new Exception("Alokasi Rp " . number_format($nominal, 0, ',', '.') . " melebihi sisa tagihan {$invoice->no_invoice}.");
                }
                $totalAlokasi += $nominal;
            }

            if ($totalAlokasi <= 0) {
                throw new Exception('Nominal alokasi pembayaran harus lebih dari 0.');
            }

            $jumlahDiterima = round($totalAlokasi - $potonganPajak, 2);
            if ($jumlahDiterima < 0) {
                throw new Exception('Potongan pajak tidak boleh melebihi total alokasi.');
            }

            $tgl = Carbon::parse($tanggal);
            $pembayaran = Pembayaran::create([
                'cabang_id' => $cabangId,
                'no_pembayaran' => NomorDokumenService::buat($cabangId, 'PAY', $tgl->format('Ym')),
                'pelanggan_id' => $pelanggan->id,
                'tanggal' => $tgl->toDateString(),
                'metode' => $metode,
                'akun_kas_id' => $akunKasId,
                'jumlah_diterima' => $jumlahDiterima,
                'potongan_pajak' => $potonganPajak,
                'dicatat_oleh' => Auth::id(),
                'status' => 'aktif',
            ]);

            foreach ($alokasis as $invoiceId => $nominal) {
                $nominal = (float) $nominal;
                if ($nominal <= 0) {
                    continue;
                }
                $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);
                PembayaranAlokasi::create([
                    'pembayaran_id' => $pembayaran->id,
                    'invoice_id' => $invoice->id,
                    'jumlah_alokasi' => $nominal,
                    'potongan_pajak' => 0,
                ]);
                $terbayarBaru = round((float) $invoice->terbayar + $nominal, 2);
                $invoice->update([
                    'terbayar' => $terbayarBaru,
                    'status' => $terbayarBaru >= (float) $invoice->total ? 'lunas' : 'sebagian',
                ]);
            }

            (new JurnalOtomatisService())->catatPembayaranPelanggan($pembayaran);

            return $pembayaran;
        });
    }
}
