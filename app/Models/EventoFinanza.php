<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventoFinanza extends Model
{
    use HasFactory;

    protected $table = 'evento_finanzas';

    protected $fillable = [
        'nombre','fecha','total_proyectado','total_neto','gastos_detalle','restante'
    ];

    protected $casts = [
        'fecha'            => 'date',
        'total_proyectado' => 'decimal:2',
        'total_neto'       => 'decimal:2',
        'restante'         => 'decimal:2',
        'gastos_detalle'   => 'array',
    ];
}
