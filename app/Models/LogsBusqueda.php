<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogsBusqueda extends Model
{
    protected $table = 'logs_busqueda'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'id'; // Llave primaria
    public $timestamps = true; // Usa `created_at` y `updated_at`

    protected $fillable = [
        'vin', // Campos rellenables
        'fecha_busqueda',
        'origen',
        'usuario'
    ];

}
