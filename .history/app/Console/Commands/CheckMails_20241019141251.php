<?php

namespace App\Console\Commands;

use App\Models\EmailLog;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

                // Verificar si el mensaje se recibió en los últimos 10 minutos
                if ($receivedDate->greaterThanOrEqualTo($tenMinutesAgo)) {
                    $this->info('Mensaje dentro de los últimos 10 minutos detectado.');
                    Log::info('Mensaje dentro de los últimos 10 minutos detectado.');

                    // Procesar adjuntos
                    foreach ($message->getAttachments() as $attachment) {
                        $this->info('Revisando archivo adjunto: ' . $attachment->name);
                        Log::info('Revisando archivo adjunto: ' . $attachment->name);

                        if (strtolower($attachment->getExtension()) == 'json') {
                            $this->info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);
                            Log::info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);

                            // Guardar en la base de datos
                            try {
                                EmailLog::create([
                                    'sender_email' => $message->getFrom()[0]->mail,
                                    'received_at' => $receivedDate, // Guardar la fecha de recepción real
                                    'file_name' => $attachment->name,
                                    'file_size' => $attachment->getSize(),
                                ]);

                                $this->info('Correo con adjunto .json guardado en la base de datos.');
                                Log::info('Correo con adjunto .json guardado en la base de datos.');
                            } catch (\Exception $e) {
                                $this->error('Error al guardar en la base de datos: ' . $e->getMessage());
                                Log::error('Error al guardar en la base de datos: ' . $e->getMessage());
                            }
                        } else {
                            $this->info('El adjunto no es un archivo .json.');
                            Log::info('El adjunto no es un archivo .json.');
                        }
                    }
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
