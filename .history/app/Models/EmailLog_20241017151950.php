<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;


    // Nombre de la tabla asociada
    protected $table = 'email_logs';

    // Campos que se pueden asignar en masa (mass assignable)
    protected $fillable = [
        'sender_email',
        'received_at',
        'file_name',
        'file_size',
    ];

    // Definir los campos de tipo fecha para las columnas
    protected $dates = ['received_at', 'created_at', 'updated_at'];
}
