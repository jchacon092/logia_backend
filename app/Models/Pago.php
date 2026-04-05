<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'miembro_id',
        'fecha_pago',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'concepto',
        'descripcion',
        'anio_recibo',
        'numero_recibo',
        'user_id',
    ];

    protected $casts = [
        'fecha_pago'    => 'date',
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'monto'         => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function miembro()
    {
        return $this->belongsTo(Miembro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Código de recibo formateado: "2026-015" */
    public function getCodigoReciboAttribute(): string
    {
        return $this->anio_recibo . '-' . str_pad($this->numero_recibo, 3, '0', STR_PAD_LEFT);
    }
}
