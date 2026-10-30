<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->first()->name ?? 'Guest';
        $cabang = $user->cabang ? $user->cabang->nama : 'Pusat';
        
        // Dummy stats for now
        $stats = [
            'total_pesanan' => 120,
            'pengiriman_aktif' => 15,
            'kendaraan_tersedia' => 8,
        ];

        return view('dashboard.index', compact('user', 'role', 'cabang', 'stats'));
    }
}