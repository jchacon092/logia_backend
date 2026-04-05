<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rubro extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];

    public function cuotaRubros()
    {
        return $this->hasMany(CuotaRubro::class);
    }
}
