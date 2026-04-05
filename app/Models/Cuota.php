<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cuota extends Model
{
    use HasFactory;

    protected $fillable = ['miembro_id','fecha','monto','concepto'];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function miembro()
    {
        return $this->belongsTo(Miembro::class);
    }

    public function asignaciones()  // ó cuotaRubros
    {
        return $this->hasMany(CuotaRubro::class);
    }
}

