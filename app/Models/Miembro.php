<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Miembro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'miembros';

    protected $fillable = [
        'nombre_completo',
        'grado',
        'email',
        'telefono',
        'direccion',
        'dpi',
        'estado_civil',
        'fecha_ingreso',
        'estado',
        'motivo_baja',
        'foto',
        'user_id',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
