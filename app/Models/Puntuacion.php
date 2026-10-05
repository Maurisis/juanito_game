<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puntuacion extends Model
{
    // Fuerza a Laravel a usar este nombre exacto de tabla
    protected $table = 'puntuaciones';

    // Autoriza a Laravel a guardar datos en estas 3 columnas
    protected $fillable = ['nivel_alcanzado', 'estado', 'tiempo_restante', 'puntuacion_final'];
}
