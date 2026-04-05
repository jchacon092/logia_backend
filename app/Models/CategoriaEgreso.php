<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaEgreso extends Model
{
    use HasFactory;

    protected $table = 'categorias_egreso';

    protected $fillable = ['nombre'];

    public function egresos()
    {
        return $this->hasMany(Egreso::class);
    }
}