<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioGranLogia extends Model {
    use HasFactory;
    protected $table = 'envios_gran_logia';
    protected $fillable = ['fecha_envio','folio','descripcion'];
    protected $casts = ['fecha_envio'=>'date'];
}
