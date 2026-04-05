<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donacion extends Model {
    use HasFactory;
    protected $fillable = ['fecha','beneficiario','monto','nota'];
    protected $casts = ['fecha'=>'date','monto'=>'decimal:2'];
}
