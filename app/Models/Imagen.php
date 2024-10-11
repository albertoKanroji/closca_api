<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    // Nombre de la tabla asociada
    protected $table = 'imagenes';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'id',
        'folio',
        'id_imagen',
        '_key',
        'adjunto',
        'FileName',
        'link_src',
        'link_thumb',
        'descripcion',
        '_label'
    ];

    // Si el campo "id" no es auto-incremental
    public $incrementing = false;

    // Si la clave primaria no es un entero
    protected $keyType = 'int';



    // Definir la relación con el modelo Auto
    public function auto()
    {
        return $this->belongsTo(Auto::class, '_key', '_key');
    }
}
