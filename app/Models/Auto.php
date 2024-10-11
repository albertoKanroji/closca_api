<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auto extends Model
{
    // Nombre de la tabla asociada
    protected $table = 'autos';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'id_auto',
        'vin',
        'f_ingreso',
        'f_salida',
        'id_cliente',
        'id_contacto',
        'id_marca',
        'modelo',
        'color',
        'color_ext',
        'pais_destino',
        'repuve',
        'accesorios',
        'id_patio',
        'fila',
        'viaje',
        'posicion',
        '_localizacion',
        'referencia',
        'codigo',
        'inspeccion',
        'comentarios',
        'estado',
        '_key',
        'tarifa',
        'nivel',
        'id_usuario',
        'dmg_codigo',
        'dmg_clasificacion',
        'dmg_descripcion',
        'dmg_modo',
        'dmg_maniobra',
        'dmg_transporte',
        'dmg_responsable',
        'rep_reparable',
        'rep_responsable',
        'rep_fecha_autorizacion',
        'rep_fecha_liberacion',
        'rep_dias',
        'rep_orden_servicio',
        'rep_requiere_partes',
        'rep_fecha_orden_partes',
        'rep_control_pedido',
        'rep_fecha_entrega_partes',
        'rep_fecha_termino',
        'rep_estado',
        'rep_comentarios',
        'rep_sitio',
        'ac_llave_tarjeta',
        'ac_tarjeta_memoria',
        'ac_7KW_charger',
        'ac_chaleco_reflectante',
        'ac_triangulo_adv',
        'ac_montaje_gato',
        'ac_gancho_remolque',
        'ac_gancho_traccion',
        'ac_llave_inteligente',
        'ac_pinza_desmontaje_dec',
        'ac_red_neumatico',
        'ac_limpiaparabrisas_izq',
        'ac_llave_inglesa',
        'ac_cierre',
        'ac_limpiaparabrisas_der',
        'ac_clips_decorativos',
        'ac_manual_usuario',
        'ac_barra_remota_gato',
        'comentario_1',
        'comentario_2',
        'comentario_3',
        'lavado_presion',
        'voltajeBateria',
        'barco',
        'modelo_ext',
        'dealer',
        'firma'
    ];

    // Si el campo "id_auto" no es auto-incremental
    protected $primaryKey = 'id_auto';
    public $incrementing = false;

    // Si la clave primaria no es un entero
    protected $keyType = 'string';


}
