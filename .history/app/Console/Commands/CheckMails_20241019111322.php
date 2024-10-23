<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog;
use Carbon\Carbon;

class CheckMails extends Command
{
    protected $signature = 'mails:check';
    protected $description = 'Check emails continuously for .json attachment and move them to "JSON RECIBIDOS" folder';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Iniciando el proceso de escucha continua para correos con adjuntos .json.');

        // Loop infinito para seguir ejecutando el proceso
        while (true) {
            try {
                // Conectar al cliente IMAP
                $client = Client::account('default');
                $client->connect();

                Log::info('Conexión al servidor IMAP exitosa.');

                // Obtener la carpeta de la bandeja de entrada
                $folder = $client->getFolder('INBOX');

                // Obtener los mensajes no leídos
                $messages = $folder->messages()->unseen()->get();
                Log::info('Número de mensajes no leídos encontrados: ' . count($messages));

                foreach ($messages as $message) {
                    $receivedDate = Carbon::parse($message->getDate())->setTimezone(config('app.timezone'));
                    Log::info('Revisando mensaje recibido en: ' . $receivedDate);

                    foreach ($message->getAttachments() as $attachment) {
                        Log::info('Revisando archivo adjunto: ' . $attachment->name);

                        // Verificar si el adjunto es un archivo .json
                        if ($attachment->getExtension() == 'json') {
                            Log::info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);

                            // Guardar en la base de datos
                            EmailLog::create([
                                'sender_email' => $message->getFrom()[0]->mail,
                                'received_at' => $receivedDate,
                                'file_name' => $attachment->name,
                                'file_size' => $attachment->getSize(),
                            ]);

                            Log::info('Correo con adjunto .json guardado en la base de datos.');

                            // Verificar si la carpeta "JSON RECIBIDOS" existe, si no, crearla
                            $jsonFolder = $client->getFolder('JSON RECIBIDOS');
                            if (!$jsonFolder) {
                                Log::info('La carpeta "JSON RECIBIDOS" no existe. Creando la carpeta...');
                                $client->createFolder('INBOX.JSON RECIBIDOS');
                                $jsonFolder = $client->getFolder('JSON RECIBIDOS');
                            }

                            // Mover el correo a la carpeta "JSON RECIBIDOS"
                            Log::info('Moviendo correo con adjunto .json a la carpeta "JSON RECIBIDOS".');
                            $message->moveToFolder($jsonFolder);

                            Log::info('Correo movido exitosamente.');
                        } else {
                            Log::info('El adjunto no es un archivo .json.');
                        }
                    }
                }

                $client->disconnect();
                Log::info('Desconexión del servidor IMAP completada.');

            } catch (\Exception $e) {
                Log::error('Error en el proceso de revisión de correos: ' . $e->getMessage());
            }

            // Dormir por unos segundos antes de revisar nuevamente para evitar saturar el servidor
            sleep(10); // Puede ajustar el tiempo según tu preferencia
        }
    }
}
