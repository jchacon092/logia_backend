<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\EnvioGranLogia;
use App\Models\Trazado;
use Illuminate\Http\Request;

class SecretariaController extends Controller
{
    // --- Trazados ---
    public function trazadosIndex(Request $r) {
        $q = Trazado::query();
        if ($r->filled('desde')) $q->whereDate('fecha','>=',$r->desde);
        if ($r->filled('hasta')) $q->whereDate('fecha','<=',$r->hasta);
        return $q->latest('fecha')->paginate(20);
    }

    public function trazadosStore(Request $r) {
        $data = $r->validate([
            'titulo'=>'required|string',
            'fecha'=>'required|date',
            'ponente'=>'required|string',
        ]);
        return Trazado::create($data);
    }

    // --- Asistencias ---
    public function asistenciasIndex(Request $r) {
        $q = Asistencia::with('miembro');
        if ($r->filled('fecha')) $q->whereDate('fecha',$r->fecha);
        if ($r->filled('miembro_id')) $q->where('miembro_id',$r->miembro_id);
        return $q->latest('fecha')->paginate(20);
    }

    public function asistenciasStore(Request $r) {
        $data = $r->validate([
            'fecha'=>'required|date',
            'miembro_id'=>'required|exists:miembros,id',
            'estado'=>'required|in:presente,ausente,justificado',
        ]);
        return Asistencia::create($data);
    }

    // --- Envíos a Gran Logia ---
    public function enviosIndex(Request $r) {
        return EnvioGranLogia::latest('fecha_envio')->paginate(20);
    }

    public function enviosStore(Request $r) {
        $data = $r->validate([
            'fecha_envio'=>'required|date',
            'folio'=>'nullable|string',
            'descripcion'=>'nullable|string',
        ]);
        return EnvioGranLogia::create($data);
    }
}
