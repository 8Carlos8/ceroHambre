<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedido';

    protected $fillable = [
        'id_usuario',
        'id_alimento',
        'cantidad',
        'estado',
        'resenia',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function alimento()
    {
        return $this->belongsTo(Alimento::class, 'id_alimento');
    }
}
