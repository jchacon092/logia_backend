<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model {
    use HasFactory;
    protected $fillable = ['fecha','miembro_id','estado'];
    protected $casts = ['fecha'=>'date'];
    public function miembro(){ return $this->belongsTo(Miembro::class); }
}
