<?php
namespace App\Http\Controllers;

use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index() {
        $kendaraans = Kendaraan::with('cabang')->get();
        return view('kendaraan.index', compact('kendaraans'));
    }
}