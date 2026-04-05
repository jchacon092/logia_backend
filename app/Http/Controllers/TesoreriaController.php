<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Pago;
use App\Models\Egreso;
use App\Models\CategoriaEgreso;

class TesoreriaController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // PAGOS (INGRESOS)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/tesoreria/pagos
     * Query: anio, mes, concepto, miembro_id, page
     */
    public function pagosIndex(Request $request)
    {
        $q = Pago::with('miembro:id,nombre_completo,grado', 'user:id,name');

        if ($request->filled('anio')) {
            $q->whereYear('fecha_pago', $request->integer('anio'));
        }
        if ($request->filled('mes')) {
            // Filtra pagos donde el mes cae dentro del rango fecha_inicio..fecha_fin
            $anio = $request->filled('anio') ? $request->integer('anio') : now()->year;
            $mes  = $request->integer('mes');
            $primerDia = sprintf('%04d-%02d-01', $anio, $mes);
            $q->where('fecha_inicio', '<=', $primerDia)
              ->where('fecha_fin',    '>=', $primerDia);
        }
        if ($request->filled('concepto')) {
            $q->where('concepto', $request->string('concepto'));
        }
        if ($request->filled('miembro_id')) {
            $q->where('miembro_id', $request->integer('miembro_id'));
        }

        $qTotals = clone $q;
        $rows    = $q->latest('fecha_pago')->paginate(20);

        return response()->json([
            'data'    => $rows->items(),
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
            'totales' => [
                'monto_total' => (float) $qTotals->sum('monto'),
                'count'       => (int)   $qTotals->count(),
            ],
        ]);
    }

    /**
     * POST /api/tesoreria/pagos
     * Body: { miembro_id?, fecha_pago, fecha_inicio, fecha_fin,
     *         monto, concepto?, descripcion? }
     * Auto-genera numero_recibo correlativo por anio_recibo.
     */
    public function pagosStore(Request $request)
    {
        $data = $request->validate([
            'miembro_id'  => ['nullable', 'exists:miembros,id'],
            'fecha_pago'  => ['required', 'date'],
            'fecha_inicio'=> ['required', 'date'],
            'fecha_fin'   => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'monto'       => ['required', 'numeric', 'min:0.01'],
            'concepto'    => ['nullable', 'string'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $anio = (int) date('Y', strtotime($data['fecha_pago']));

        $pago = DB::transaction(function () use ($data, $anio, $request) {
            // Número correlativo con bloqueo para evitar duplicados en concurrencia
            $ultimo = Pago::where('anio_recibo', $anio)
                          ->lockForUpdate()
                          ->max('numero_recibo');

            return Pago::create(array_merge($data, [
                'anio_recibo'   => $anio,
                'numero_recibo' => ($ultimo ?? 0) + 1,
                'user_id'       => $request->user()?->id,
                'concepto'      => $data['concepto'] ?? 'mensualidad',
            ]));
        });

        return response()->json(
            $pago->load('miembro:id,nombre_completo,grado'),
            201
        );
    }

    /**
     * GET /api/tesoreria/pagos/{pago}/recibo
     * Devuelve todos los datos necesarios para generar el PDF del recibo.
     */
    public function reciboShow(Pago $pago)
    {
        $pago->load('miembro:id,nombre_completo,grado', 'user:id,name');

        return response()->json([
            'codigo_recibo'  => $pago->codigo_recibo,
            'numero_recibo'  => $pago->numero_recibo,
            'anio_recibo'    => $pago->anio_recibo,
            'fecha_pago'     => $pago->fecha_pago->format('d/m/Y'),
            'miembro'        => $pago->miembro ? [
                'nombre' => $pago->miembro->nombre_completo,
                'grado'  => $pago->miembro->grado,
            ] : null,
            'monto'          => (float) $pago->monto,
            'concepto'       => $pago->concepto,
            'descripcion'    => $pago->descripcion,
            'fecha_inicio'   => $pago->fecha_inicio->format('Y-m-d'),
            'fecha_fin'      => $pago->fecha_fin->format('Y-m-d'),
            'registrado_por' => $pago->user?->name,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EGRESOS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/tesoreria/egresos
     * Query: anio, mes, categoria_egreso_id, page
     */
    public function egresosIndex(Request $request)
    {
        $q = Egreso::with('categoria:id,nombre', 'user:id,name');

        if ($request->filled('anio')) {
            $q->whereYear('fecha', $request->integer('anio'));
        }
        if ($request->filled('mes')) {
            $q->whereMonth('fecha', $request->integer('mes'));
        }
        if ($request->filled('categoria_egreso_id')) {
            $q->where('categoria_egreso_id', $request->integer('categoria_egreso_id'));
        }

        $qTotals = clone $q;
        $rows    = $q->latest('fecha')->paginate(20);

        return response()->json([
            'data'    => $rows->items(),
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
            'totales' => [
                'monto_total' => (float) $qTotals->sum('monto'),
                'count'       => (int)   $qTotals->count(),
            ],
        ]);
    }

    /**
     * POST /api/tesoreria/egresos
     * Body: { categoria_egreso_id, descripcion, monto, fecha, referencia? }
     */
    public function egresosStore(Request $request)
    {
        $data = $request->validate([
            'categoria_egreso_id' => ['required', 'exists:categorias_egreso,id'],
            'descripcion'         => ['required', 'string', 'max:500'],
            'monto'               => ['required', 'numeric', 'min:0.01'],
            'fecha'               => ['required', 'date'],
            'referencia'          => ['nullable', 'string', 'max:100'],
        ]);

        $egreso = Egreso::create(array_merge($data, [
            'user_id' => $request->user()?->id,
        ]));

        return response()->json(
            $egreso->load('categoria:id,nombre'),
            201
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CATEGORÍAS DE EGRESO
    // ══════════════════════════════════════════════════════════════════════════

    /** GET /api/tesoreria/categorias-egreso */
    public function categoriasIndex()
    {
        return response()->json(
            CategoriaEgreso::orderBy('nombre')->get()
        );
    }

    /** POST /api/tesoreria/categorias-egreso */
    public function categoriasStore(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('categorias_egreso', 'nombre')],
        ]);

        return response()->json(CategoriaEgreso::create($data), 201);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RESUMEN / BALANCE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/tesoreria/resumen?anio=YYYY&mes=M (mes es opcional)
     * Devuelve: total_ingresos, total_egresos, balance, desglose por categoría de egreso.
     */
    public function resumen(Request $request)
    {
        $request->validate([
            'anio' => ['required', 'integer', 'min:2000', 'max:2999'],
            'mes'  => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $anio = $request->integer('anio');
        $mes  = $request->filled('mes') ? $request->integer('mes') : null;

        // ── Ingresos ──────────────────────────────────────────────────────────
        $qPagos = Pago::whereYear('fecha_pago', $anio);
        if ($mes) $qPagos->whereMonth('fecha_pago', $mes);
        $totalIngresos = (float) $qPagos->sum('monto');

        $porConcepto = Pago::whereYear('fecha_pago', $anio)
            ->when($mes, fn($q) => $q->whereMonth('fecha_pago', $mes))
            ->selectRaw('concepto, SUM(monto) as total, COUNT(*) as count')
            ->groupBy('concepto')
            ->get();

        // ── Egresos ───────────────────────────────────────────────────────────
        $qEgresos = Egreso::whereYear('fecha', $anio);
        if ($mes) $qEgresos->whereMonth('fecha', $mes);
        $totalEgresos = (float) $qEgresos->sum('monto');

        $porCategoria = Egreso::whereYear('fecha', $anio)
            ->when($mes, fn($q) => $q->whereMonth('fecha', $mes))
            ->with('categoria:id,nombre')
            ->selectRaw('categoria_egreso_id, SUM(monto) as total, COUNT(*) as count')
            ->groupBy('categoria_egreso_id')
            ->get()
            ->map(fn($r) => [
                'categoria_id'  => $r->categoria_egreso_id,
                'categoria'     => $r->categoria?->nombre,
                'total'         => (float) $r->total,
                'count'         => (int)   $r->count,
            ]);

        return response()->json([
            'anio'            => $anio,
            'mes'             => $mes,
            'total_ingresos'  => $totalIngresos,
            'total_egresos'   => $totalEgresos,
            'balance'         => $totalIngresos - $totalEgresos,
            'por_concepto'    => $porConcepto,
            'por_categoria'   => $porCategoria,
        ]);
    }
}
