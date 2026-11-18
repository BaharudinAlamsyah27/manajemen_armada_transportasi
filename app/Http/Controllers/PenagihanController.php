<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Invoice;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Tarif;
use App\Services\NomorDokumenService;
use App\Services\PenagihanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;

class PenagihanController extends Controller
{
    public function index()
    {
        $pelanggans = Pelanggan::orderBy('nama')->get();
        $tarifs = Tarif::orderBy('kota_asal')->get();
        $invoices = Invoice::with('pelanggan')->latest('tanggal')->get();
        $pesanansSiap = Pesanan::where('status', 'terkirim')->latest()->get();
        $akunKas = Akun::where('is_kas', true)->where('aktif', true)->get();

        return view('penagihan.index', compact('pelanggans', 'tarifs', 'invoices', 'pesanansSiap', 'akunKas'));
    }

    public function storePelanggan(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:120',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:80',
            'no_hp' => 'nullable|string|max:30',
            'termin_hari' => 'required|integer|min:0|max:120',
        ]);
        $validated['kode'] = NomorDokumenService::buat(null, 'PLG');
        Pelanggan::create($validated);

        return back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function storeTarif(Request $request)
    {
        $validated = $request->validate([
            'kota_asal' => 'required|string|max:80',
            'kota_tujuan' => 'required|string|max:80',
            'harga_per_kg' => 'required|numeric|min:1',
            'tarif_minimum' => 'required|numeric|min:0',
        ]);
        $validated['berlaku_sejak'] = now()->toDateString();
        Tarif::create($validated);

        return back()->with('success', 'Master tarif berhasil ditambahkan.');
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'pesanan_ids' => 'required|array|min:1',
            'ppn_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $inv = (new PenagihanService())->buatInvoice(
                (int) $validated['pelanggan_id'],
                $validated['pesanan_ids'],
                $validated['ppn_persen'] !== null ? (float) $validated['ppn_persen'] : null
            );
            return back()->with('success', "Invoice {$inv->no_invoice} berhasil diterbitkan.");
        } catch (Exception $e) {
            return back()->withErrors(['invoice' => $e->getMessage()]);
        }
    }

    public function bayarInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'akun_kas_id' => 'required|exists:akuns,id',
            'jumlah' => 'required|numeric|min:1',
            'potongan_pajak' => 'nullable|numeric|min:0',
        ]);

        try {
            (new PenagihanService())->catatPembayaran(
                $invoice->pelanggan_id,
                (int) $validated['akun_kas_id'],
                [$invoice->id => (float) $validated['jumlah']],
                now()->toDateString(),
                'transfer',
                (float) ($validated['potongan_pajak'] ?? 0)
            );
            return back()->with('success', "Pembayaran untuk {$invoice->no_invoice} tercatat.");
        } catch (Exception $e) {
            return back()->withErrors(['bayar' => $e->getMessage()]);
        }
    }

    public function cetakInvoice(Invoice $invoice)
    {
        $invoice->load(['pelanggan', 'items']);
        $pdf = Pdf::loadView('penagihan.invoice-pdf', compact('invoice'));
        return $pdf->download("{$invoice->no_invoice}.pdf");
    }
}
