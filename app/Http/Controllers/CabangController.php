<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    public function index()
    {
        $cabangs = Cabang::orderBy('kode')->get();
        return view('cabang.index', compact('cabangs'));
    }

    public function create()
    {
        return view('cabang.form', ['cabang' => new Cabang()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:cabangs,kode',
            'nama' => 'required|string|max:100',
            'kota' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:30',
            'aktif' => 'nullable|boolean',
        ]);

        $validated['kode'] = strtoupper($validated['kode']);
        $validated['aktif'] = $request->boolean('aktif', true);

        Cabang::create($validated);

        return redirect()->route('cabang.index')->with('success', 'Data cabang berhasil ditambahkan.');
    }

    public function edit(Cabang $cabang)
    {
        return view('cabang.form', compact('cabang'));
    }

    public function update(Request $request, Cabang $cabang)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:cabangs,kode,' . $cabang->id,
            'nama' => 'required|string|max:100',
            'kota' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:30',
            'aktif' => 'nullable|boolean',
        ]);

        $validated['kode'] = strtoupper($validated['kode']);
        $validated['aktif'] = $request->boolean('aktif');

        $cabang->update($validated);

        return redirect()->route('cabang.index')->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Cabang $cabang)
    {
        if ($cabang->kendaraans()->exists() || $cabang->karyawans()->exists() || $cabang->pesanans()->exists()) {
            $cabang->update(['aktif' => false]);
            return redirect()->route('cabang.index')->with('success', 'Cabang memiliki data berelasi, status diubah menjadi nonaktif.');
        }

        $cabang->delete();
        return redirect()->route('cabang.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
