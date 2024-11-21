<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmgDetalle extends Model
{
    protected $table = 'dmg_detalle'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'id'; // Llave primaria
    public $timestamps = true; // Usa `created_at` y `updated_at`

    protected $fillable = [
        'comentario', // Campos rellenables
        'dmg_codigo',
        'autos_id_auto'
    ];

    /**
     * Relación con Auto (pertenece a).
     */
    public function auto()
    {
        return $this->belongsTo(Auto::class, 'autos_id_auto', 'id_auto');
    }
}
