<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';

    protected $fillable = [
        'categoria_egreso_id',
        'descripcion',
        'monto',
        'fecha',
        'referencia',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaEgreso::class, 'categoria_egreso_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}