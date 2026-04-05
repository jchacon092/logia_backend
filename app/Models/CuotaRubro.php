<?php

// app/Models/CuotaRubro.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuotaRubro extends Model
{
    use HasFactory;

    protected $table = 'cuota_rubros';

    protected $fillable = [
        'cuota_id','rubro_id','monto','checked','nota'
    ];

    protected $casts = [
        'monto'   => 'decimal:2',
        'checked' => 'boolean',
    ];

    public function cuota() { return $this->belongsTo(Cuota::class); }
    public function rubro() { return $this->belongsTo(Rubro::class); }
}
