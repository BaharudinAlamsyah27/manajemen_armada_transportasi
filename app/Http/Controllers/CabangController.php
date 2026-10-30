<?php
namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    public function index() {
        $cabangs = Cabang::all();
        return view('cabang.index', compact('cabangs'));
    }
}