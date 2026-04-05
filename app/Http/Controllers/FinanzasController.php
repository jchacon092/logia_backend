<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Cuota;
use App\Models\EventoFinanza;
use App\Models\Rubro;        // Tabla: rubros
use App\Models\CuotaRubro;   // Tabla: cuota_rubros (detalle bitácora)

class FinanzasController extends Controller
{
    /**
     * GET /api/finanzas/cuotas (auth + permission: finanzas.view)
     * Query: anio, mes, miembro_id, concepto
     */
    public function index(Request $request)
    {
        $q = Cuota::with('miembro');

        if ($request->filled('anio')) {
            $q->whereYear('fecha', $request->integer('anio'));
        }
        if ($request->filled('mes')) {
            $q->whereMonth('fecha', $request->integer('mes'));
        }
        if ($request->filled('miembro_id')) {
            $q->where('miembro_id', $request->integer('miembro_id'));
        }
        if ($request->filled('concepto')) {
            $q->where('concepto', 'like', '%'.$request->string('concepto').'%');
        }

        $qTotals = (clone $q);
        $rows    = $q->latest('fecha')->paginate(20);

        $totales = [
            'monto_total' => (float) $qTotals->sum('monto'),
            'count'       => (int)   $qTotals->count(),
        ];

        return response()->json([
            'data'    => $rows->items(),
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
            'totales' => $totales,
        ]);
    }

    /**
     * POST /api/finanzas/cuotas (auth + permission: finanzas.edit)
     * Body: { miembro_id, fecha (Y-m-d), monto, concepto? }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'miembro_id' => ['required','exists:miembros,id'],
            'fecha'      => ['required','date'],
            'monto'      => ['required','numeric','min:0'],
            'concepto'   => ['nullable','string'],
        ]);

        $row = Cuota::create($data);
        return response()->json($row, 201);
    }

    /**
     * GET /api/finanzas/eventos (auth + permission: finanzas.view)
     * Query: anio, mes
     */
    public function eventosIndex(Request $request)
    {
        $q = EventoFinanza::query();

        if ($request->filled('anio')) {
            $q->whereYear('fecha', $request->integer('anio'));
        }
        if ($request->filled('mes')) {
            $q->whereMonth('fecha', $request->integer('mes'));
        }

        $qTotals = (clone $q);
        $rows    = $q->latest('fecha')->paginate(20);

        $totales = [
            'total_proyectado' => (float) (clone $qTotals)->sum('total_proyectado'),
            'total_neto'       => (float) (clone $qTotals)->sum('total_neto'),
            'restante'         => (float) (clone $qTotals)->sum('restante'),
        ];

        return response()->json([
            'data'    => $rows->items(),
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
            'totales' => $totales,
        ]);
    }

    /**
     * POST /api/finanzas/eventos (auth + permission: finanzas.edit)
     * Body:
     * {
     *   nombre, fecha (Y-m-d),
     *   total_proyectado?, total_neto?,
     *   gastos_detalle?: [{ item, costo }]
     * }
     * Calcula restante = total_neto - sum(gastos.costo)
     */
    public function eventosStore(Request $request)
    {
        $data = $request->validate([
            'nombre'                 => ['required','string'],
            'fecha'                  => ['required','date'],
            'total_proyectado'       => ['nullable','numeric','min:0'],
            'total_neto'             => ['nullable','numeric','min:0'],
            'gastos_detalle'         => ['nullable','array'],
            'gastos_detalle.*.item'  => ['required_with:gastos_detalle','string'],
            'gastos_detalle.*.costo' => ['required_with:gastos_detalle','numeric','min:0'],
        ]);

        $gastos     = collect($data['gastos_detalle'] ?? []);
        $sumaGastos = (float) $gastos->sum('costo');
        $totalNeto  = (float) ($data['total_neto'] ?? 0);

        $data['restante'] = $totalNeto - $sumaGastos;

        $row = EventoFinanza::create($data);
        return response()->json($row, 201);
    }

    /* =========================================================
     * RUBROS / BITÁCORA / RESUMEN MENSUAL
     * =======================================================*/

    /** GET /api/finanzas/rubros */
    public function rubrosIndex()
    {
        return response()->json(
            Rubro::orderBy('nombre')->get()
        );
    }

    /** GET /api/finanzas/cuotas/{cuota}/bitacora */
    public function cuotaBitacoraShow(Cuota $cuota)
    {
        try {
            $items = $cuota->asignaciones()   // relación: hasMany(CuotaRubro::class, 'cuota_id')
                ->with('rubro:id,nombre')     // relación en CuotaRubro: belongsTo(Rubro::class, 'rubro_id')
                ->orderBy('rubro_id')
                ->get()
                ->map(fn($cr) => [
                    'rubro_id' => (int) $cr->rubro_id,
                    'rubro'    => (string) ($cr->rubro?->nombre ?? ''),
                    'monto'    => (float) $cr->monto,
                    'checked'  => (bool)  $cr->checked,
                    'nota'     => $cr->nota,
                ]);

            return response()->json([
                'cuota_id' => (int) $cuota->id,
                'monto'    => (float) $cuota->monto,
                'items'    => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('cuotaBitacoraShow error: '.$e->getMessage(), ['cuota_id' => $cuota->id]);
            // No rompas el front: responde vacío si algo sale mal
            return response()->json([
                'cuota_id' => (int) $cuota->id,
                'monto'    => (float) $cuota->monto,
                'items'    => [],
            ], 200);
        }
    }

    /**
     * PUT /api/finanzas/cuotas/{cuota}/bitacora
     * Body:
     * { items: [{ rubro_id, monto, checked, nota? }, ...] }
     * - Se guardan SOLO los items checked=true
     * - La suma de montos (solo checked) no puede exceder el monto de la cuota
     * - Reemplaza completamente las asignaciones de la cuota
     */
    public function cuotaBitacoraUpsert(Request $request, Cuota $cuota)
    {
        $data = $request->validate([
            'items'                 => ['required','array'], // puede ser vacío para "limpiar"
            'items.*.rubro_id'      => ['required','integer', Rule::exists('rubros','id')],
            'items.*.monto'         => ['required','numeric','min:0'],
            'items.*.checked'       => ['required','boolean'],
            'items.*.nota'          => ['nullable','string','max:250'],
        ]);

        $soloChecked = collect($data['items'])->filter(fn($it) => !!$it['checked'])->values();
        $suma        = (float) $soloChecked->sum('monto');

        if ($suma > (float) $cuota->monto) {
            return response()->json([
                'message' => 'El total asignado supera el monto de la cuota',
                'suma'    => $suma,
                'monto'   => (float) $cuota->monto,
            ], 422);
        }

        DB::transaction(function () use ($cuota, $soloChecked) {
            // Borra todas las asignaciones anteriores
            CuotaRubro::where('cuota_id', $cuota->id)->delete();

            if ($soloChecked->isEmpty()) {
                return; // nada que insertar
            }

            $now  = now();
            $rows = $soloChecked->map(function ($it) use ($cuota, $now) {
                return [
                    'cuota_id'   => (int)    $cuota->id,
                    'rubro_id'   => (int)    $it['rubro_id'],
                    'monto'      => (float)  $it['monto'],
                    'checked'    => true,
                    'nota'       => $it['nota'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            CuotaRubro::insert($rows);
        });

        // Devuelve la bitácora actualizada
        return $this->cuotaBitacoraShow($cuota);
    }

    /**
     * GET /api/finanzas/resumen?anio=YYYY&mes=M
     * Respuesta:
     * {
     *   anio, mes,
     *   total_recaudado,
     *   total_asignado,
     *   restante,
     *   por_rubro: [{ rubro_id, rubro, total_asignado }]
     * }
     */
    public function resumenMensual(Request $request)
    {
        $request->validate([
            'anio' => ['required','integer','min:1900','max:2999'],
            'mes'  => ['required','integer','min:1','max:12'],
        ]);
        $anio = (int) $request->integer('anio');
        $mes  = (int) $request->integer('mes');

        $totalRecaudado = (float) Cuota::query()
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->sum('monto');

        $asignaciones = CuotaRubro::query()
            ->whereHas('cuota', fn($q) =>
                $q->whereYear('fecha', $anio)->whereMonth('fecha', $mes)
            )
            ->where('checked', true)
            ->with('rubro:id,nombre')
            ->get();

        $totalAsignado = (float) $asignaciones->sum('monto');

        $porRubro = $asignaciones
            ->groupBy('rubro_id')
            ->map(function ($rows, $rubroId) {
                return [
                    'rubro_id'        => (int) $rubroId,
                    'rubro'           => (string) optional($rows->first()->rubro)->nombre,
                    'total_asignado'  => (float) $rows->sum('monto'),
                ];
            })
            ->values()
            ->sortBy('rubro') // orden alfabético por nombre
            ->values();

        return response()->json([
            'anio'            => $anio,
            'mes'             => $mes,
            'total_recaudado' => $totalRecaudado,
            'total_asignado'  => $totalAsignado,
            'restante'        => $totalRecaudado - $totalAsignado,
            'por_rubro'       => $porRubro,
        ]);
    }
}
