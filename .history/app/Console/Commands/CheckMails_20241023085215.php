<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Auto;
use App\Models\Imagen;
class CheckMails extends Command
{
    protected $signature = 'mails:check';
    protected $description = 'Check emails continuously for .json attachment and log them';

    public function __construct()
    {
        parent::__construct();
    }



    public function handle()
    {
        try {
            $this->info('Iniciando el proceso de revisión de correos de los últimos 10 minutos.');
            Log::info('Iniciando el proceso de revisión de correos de los últimos 10 minutos.');

            // Conexión al cliente IMAP
            $client = Client::account('default');
            $client->connect();
            $this->info('Conexión al servidor IMAP exitosa.');
            Log::info('Conexión al servidor IMAP exitosa.');

            // Obtener la carpeta INBOX
            $folder = $client->getFolder('INBOX');
            $tenMinutesAgo = Carbon::now()->subMinutes(10)->timezone('UTC'); // Asegurar la zona horaria

            $this->info('Obteniendo mensajes no leídos desde los últimos 10 minutos: ' . $tenMinutesAgo);
            Log::info('Obteniendo mensajes no leídos desde los últimos 10 minutos: ' . $tenMinutesAgo);

            // Obtener correos no leídos desde los últimos 10 minutos
            try {
                $messages = $folder->messages()->unseen()->since($tenMinutesAgo)->get();
                $totalMessages = count($messages);
                $this->info('Número de mensajes no leídos encontrados: ' . $totalMessages);
                Log::info('Número de mensajes no leídos encontrados: ' . $totalMessages);
            } catch (\Exception $e) {
                $this->error('Error al obtener los mensajes: ' . $e->getMessage());
                Log::error('Error al obtener los mensajes: ' . $e->getMessage());
                return;
            }

            // Procesar cada mensaje
            foreach ($messages as $message) {
                try {
                    // Convertir la fecha del mensaje a UTC para hacer una comparación precisa
                    $receivedDate = Carbon::parse($message->getDate())->timezone('UTC');
                    $this->info('Revisando mensaje recibido en: ' . $receivedDate);
                    Log::info('Revisando mensaje recibido en: ' . $receivedDate);

                    // Procesar adjuntos
                    if ($message->hasAttachments()) {
                        $this->info('El mensaje tiene archivos adjuntos.');
                        Log::info('El mensaje tiene archivos adjuntos.');

                        // Procesar adjuntos
                        foreach ($message->getAttachments() as $attachment) {
                            $this->info('Revisando archivo adjunto: ' . $attachment->name);
                            Log::info('Revisando archivo adjunto: ' . $attachment->name);

                            // Verificar si el adjunto es un archivo .json
                            if (strtolower($attachment->getExtension()) == 'json') {
                                $fromEmail = $message->getFrom()[0]->mail;
                                $this->info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);
                                Log::info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);
                                $trackingHash = hash('sha1', $fromEmail . Carbon::now());
                                // Crear la carpeta con la fecha actual
                                $folderName = Carbon::now()->format('Y-m-d');
                                $filePath = "storage/app/$folderName/" . $attachment->name;

                                // Guardar el archivo JSON en la carpeta
                                if (!Storage::exists($folderName)) {
                                    Storage::makeDirectory($folderName);
                                }
                                $content = $attachment->getContent();
                                Storage::put("$folderName/{$attachment->name}", $content);
                                // Guardar en la base de datos
                                $this->info('Archivo JSON guardado en: ' . $filePath);
                                Log::info('Archivo JSON guardado en: ' . $filePath);
                                 // *** PROCESAR EL CONTENIDO DEL JSON GUARDADO ***

                                // Leer el contenido del archivo JSON
                                $jsonContent = Storage::get("$folderName/{$attachment->name}");

                                // Decodificar el JSON
                                $data = json_decode($jsonContent, true);
                                foreach ($data['inspecciones'] as $inspeccion) {
                                    $autoData = $inspeccion['auto'];

                                    // Guardar los datos del vehículo en el modelo Auto
                                    $auto = Auto::create([
                                        'id_auto' => $autoData['Unidad']['id_auto'],
                                        'vin' => $autoData['Unidad']['vin'],
                                        'f_ingreso' => $autoData['Unidad']['f_ingreso'],
                                        'f_salida' => $autoData['Unidad']['f_salida'],
                                        'id_cliente' => $autoData['Unidad']['id_cliente'],
                                        'id_contacto' => $autoData['Unidad']['id_contacto'],
                                        'id_marca' => $autoData['Unidad']['id_marca'],
                                        'modelo' => $autoData['Unidad']['modelo'],
                                        'color' => $autoData['Unidad']['color'],
                                        'color_ext' => $autoData['Unidad']['color_ext'],
                                        'lavado_presion' => $autoData['Unidad']['lavado_presion'],
                                        'voltajeBateria' => $autoData['Unidad']['voltajeBateria'],
                                        'barco' => $autoData['Unidad']['barco'],
                                        'modelo_ext' => $autoData['Unidad']['modelo_ext'],
                                        'dealer' => $autoData['Unidad']['dealer'],
                                        'firma' => $autoData['Unidad']['firma'],
                                        // UBICACION
                                        'pais_destino' => $autoData['UBICACION']['pais_destino'],
                                        'id_patio' => $autoData['UBICACION']['id_patio'],
                                        'fila' => $autoData['UBICACION']['fila'],
                                        'viaje' => $autoData['UBICACION']['viaje'],
                                        'posicion' => $autoData['UBICACION']['posicion'],
                                        '_localizacion' => $autoData['UBICACION']['_localizacion'],
                                        'referencia' => $autoData['UBICACION']['referencia'],
                                        // DMG
                                        'dmg_codigo' => $autoData['DMG']['dmg_codigo'],
                                        'dmg_clasificacion' => $autoData['DMG']['dmg_clasificacion'],
                                        'dmg_descripcion' => $autoData['DMG']['dmg_descripcion'],
                                        'dmg_modo' => $autoData['DMG']['dmg_modo'],
                                        'dmg_maniobra' => $autoData['DMG']['dmg_maniobra'],
                                        'dmg_transporte' => $autoData['DMG']['dmg_transporte'],
                                        'dmg_responsable' => $autoData['DMG']['dmg_responsable'],
                                        // REPUVE
                                        'rep_reparable' => $autoData['REPUVE']['rep_reparable'],
                                        'rep_responsable' => $autoData['REPUVE']['rep_responsable'],
                                        'rep_fecha_autorizacion' => $autoData['REPUVE']['rep_fecha_autorizacion'],
                                        'rep_fecha_liberacion' => $autoData['REPUVE']['rep_fecha_liberacion'],
                                        'rep_dias' => $autoData['REPUVE']['rep_dias'],
                                        'rep_orden_servicio' => $autoData['REPUVE']['rep_orden_servicio'],
                                        'rep_requiere_partes' => $autoData['REPUVE']['rep_requiere_partes'],
                                        'rep_fecha_orden_partes' => $autoData['REPUVE']['rep_fecha_orden_partes'],
                                        'rep_control_pedido' => $autoData['REPUVE']['rep_control_pedido'],
                                        'rep_fecha_entrega_partes' => $autoData['REPUVE']['rep_fecha_entrega_partes'],
                                        'rep_fecha_termino' => $autoData['REPUVE']['rep_fecha_termino'],
                                        'rep_estado' => $autoData['REPUVE']['rep_estado'],
                                        'rep_comentarios' => $autoData['REPUVE']['rep_comentarios'],
                                        'rep_sitio' => $autoData['REPUVE']['rep_sitio'],
                                        // ACCESORIOS
                                        'ac_llave_tarjeta' => $autoData['ACCESORIOS']['ac_llave_tarjeta'],
                                        'ac_tarjeta_memoria' => $autoData['ACCESORIOS']['ac_tarjeta_memoria'],
                                        'ac_7KW_charger' => $autoData['ACCESORIOS']['ac_7KW_charger'],
                                        'ac_chaleco_reflectante' => $autoData['ACCESORIOS']['ac_chaleco_reflectante'],
                                        'ac_triangulo_adv' => $autoData['ACCESORIOS']['ac_triangulo_adv'],
                                        'ac_montaje_gato' => $autoData['ACCESORIOS']['ac_montaje_gato'],
                                        'ac_gancho_remolque' => $autoData['ACCESORIOS']['ac_gancho_remolque'],
                                        'ac_gancho_traccion' => $autoData['ACCESORIOS']['ac_gancho_traccion'],
                                        'ac_llave_inteligente' => $autoData['ACCESORIOS']['ac_llave_inteligente'],
                                        'ac_pinza_desmontaje_dec' => $autoData['ACCESORIOS']['ac_pinza_desmontaje_dec'],
                                        'ac_red_neumatico' => $autoData['ACCESORIOS']['ac_red_neumatico'],
                                        'ac_limpiaparabrisas_izq' => $autoData['ACCESORIOS']['ac_limpiaparabrisas_izq'],
                                        'ac_llave_inglesa' => $autoData['ACCESORIOS']['ac_llave_inglesa'],
                                        'ac_cierre' => $autoData['ACCESORIOS']['ac_cierre'],
                                        'ac_limpiaparabrisas_der' => $autoData['ACCESORIOS']['ac_limpiaparabrisas_der'],
                                        'ac_clips_decorativos' => $autoData['ACCESORIOS']['ac_clips_decorativos'],
                                        'ac_manual_usuario' => $autoData['ACCESORIOS']['ac_manual_usuario'],
                                        'ac_barra_remota_gato' => $autoData['ACCESORIOS']['ac_barra_remota_gato'],
                                        // COMENTARIOS
                                        'comentario_1' => $autoData['COMENTARIOS']['comentario_1'],
                                        'comentario_2' => $autoData['COMENTARIOS']['comentario_2'],
                                        'comentario_3' => $autoData['COMENTARIOS']['comentario_3'],
                                    ]);

                                    // Guardar las imágenes si existen
                                    if (!empty($inspeccion['imagenes'])) {
                                        foreach ($inspeccion['imagenes'] as $imagen) {
                                            Imagen::create([
                                                'folio' => $auto->id_auto,
                                                'id_imagen' => $imagen['id_imagen'],
                                                '_key' => $imagen['_key'],
                                                'adjunto' => $imagen['adjunto'],
                                                'FileName' => $imagen['FileName'],
                                                'link_src' => $imagen['link_src'],
                                                'link_thumb' => $imagen['link_thumb'],
                                                'descripcion' => $imagen['descripcion'],
                                                '_label' => $imagen['_label'],
                                            ]);
                                        }
                                    }
                                }
                                try {

                                    EmailLog::create([
                                        'sender_email' => $message->getFrom()[0]->mail,
                                        'received_at' => $receivedDate, // Guardar la fecha de recepción real
                                        'file_name' => $attachment->name,
                                        'file_size' => $attachment->getSize(),
                                        'key' => $trackingHash,
                                    ]);

                                    $this->info('Correo con adjunto .json guardado en la base de datos.');
                                    Log::info('Correo con adjunto .json guardado en la base de datos.');

                                    $this->info('Correo con adjunto .json guardado en la base de datos con clave de seguimiento.');
                                    Log::info('Correo con adjunto .json guardado en la base de datos con clave de seguimiento.');

                                    // Enviar la respuesta automática
                                    Mail::raw("SE HA GUARDADO CORRECTAMENTE EL JSON EN NUESTRA BASE DE DATOS.\n\nCLAVE DE SEGUIMIENTO: $trackingHash", function($message) use ($fromEmail) {
                                        $message->to($fromEmail)
                                                ->subject('Confirmación de recepción de JSON');
                                    });

                                    $this->info('Respuesta enviada a: ' . $fromEmail);
                                    Log::info('Respuesta enviada a: ' . $fromEmail);
                                } catch (\Exception $e) {
                                    $this->error('Error al guardar en la base de datos: ' . $e->getMessage());
                                    Log::error('Error al guardar en la base de datos: ' . $e->getMessage());
                                }
                            } else {
                                $this->info('El adjunto no es un archivo .json.');
                                Log::info('El adjunto no es un archivo .json.');
                            }
                        }
                    } else {
                        $this->info('El mensaje no tiene archivos adjuntos.');
                        Log::info('El mensaje no tiene archivos adjuntos.');
                    }
                } catch (\Exception $e) {
                    $this->error('Error procesando el mensaje: ' . $e->getMessage());
                    Log::error('Error procesando el mensaje: ' . $e->getMessage());
                }
            }

            // Desconectar del servidor IMAP
            try {
                $client->disconnect();
                $this->info('Desconexión del servidor IMAP completada.');
                Log::info('Desconexión del servidor IMAP completada.');
            } catch (\Exception $e) {
                $this->error('Error al desconectar del servidor IMAP: ' . $e->getMessage());
                Log::error('Error al desconectar del servidor IMAP: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error('Error general: ' . $e->getMessage());
            Log::error('Error general: ' . $e->getMessage());
        }
    }
}
