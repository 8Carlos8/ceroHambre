<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alimento extends Model
{
    use HasFactory;

    protected $table = 'alimento';

    protected $fillable = [
        'id_donante',
        'nombre',
        'foto',
        'descripcion',
        'cantidad',
        'fecha_vencimiento',
        'estado',
    ];

    public function donante()
    {
        return $this->belongsTo(Donante::class, 'id_donante');
    }
}
