<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donante extends Model
{
    use HasFactory;

    protected $table = 'donante';

    protected $fillable = [
        'id_usuario',
        'tipo_asociacion',
        'logo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
