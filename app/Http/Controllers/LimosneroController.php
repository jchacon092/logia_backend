<?php

namespace App\Http\Controllers;

use App\Models\Colecta;
use App\Models\Donacion;
use Illuminate\Http\Request;

class LimosneroController extends Controller
{
    // Colectas (beneficencia)
    public function colectasIndex(Request $r) {
        $q = Colecta::query();
        if ($r->filled('anio')) $q->whereYear('fecha',$r->anio);
        if ($r->filled('mes'))  $q->whereMonth('fecha',$r->mes);
        return $q->latest('fecha')->paginate(20);
    }

    public function colectasStore(Request $r) {
        $data = $r->validate([
            'fecha'=>'required|date',
            'monto'=>'required|numeric|min:0',
        ]);
        return Colecta::create($data);
    }

    // Donaciones (hospitalario)
    public function donacionesIndex(Request $r) {
        $q = Donacion::query();
        if ($r->filled('beneficiario')) $q->where('beneficiario','like','%'.$r->beneficiario.'%');
        if ($r->filled('anio')) $q->whereYear('fecha',$r->anio);
        return $q->latest('fecha')->paginate(20);
    }

    public function donacionesStore(Request $r) {
        $data = $r->validate([
            'fecha'=>'required|date',
            'beneficiario'=>'required|string',
            'monto'=>'required|numeric|min:0',
            'nota'=>'nullable|string',
        ]);
        return Donacion::create($data);
    }
}
