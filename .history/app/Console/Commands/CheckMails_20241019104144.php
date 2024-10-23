<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog; // Importar el modelo
use Carbon\Carbon;

class CheckMails extends Command
{
    protected $signature = 'mails:check';
    protected $description = 'Check emails from the last hour and alert on JSON attachment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Iniciando el proceso de revisión de correos de los últimos 10 minutos.');

        $client = Client::account('default');
        $client->connect();

        Log::info('Conexión al servidor IMAP exitosa.');

        $folder = $client->getFolder('INBOX');
        $tenMinutesAgo = Carbon::now()->subMinutes(10); // Obtener la fecha de 10 minutos atrás

        Log::info('Obteniendo mensajes no leídos desde los últimos 10 minutos: ' . $tenMinutesAgo);

        $messages = $folder->messages()->unseen()->since($tenMinutesAgo)->get();

        Log::info('Número de mensajes no leídos encontrados: ' . count($messages));

        foreach ($messages as $message) {
            // Asegurarse de que la fecha del mensaje está en la zona horaria correcta
            $receivedDate = Carbon::parse($message->getDate())->setTimezone(config('app.timezone'));

            Log::info('Revisando mensaje recibido en: ' . $receivedDate);

            // Verificamos si el mensaje se recibió en los últimos 10 minutos
            // if ($receivedDate >= $tenMinutesAgo) {
            Log::info('Mensaje dentro de los últimos 10 minutos detectado.');

            foreach ($message->getAttachments() as $attachment) {
                Log::info('Revisando archivo adjunto: ' . $attachment->name);

                if ($attachment->getExtension() == 'json') {
                    Log::info('Adjunto .json detectado en correo de: ' . $message->getFrom()[0]->mail);

                    // Guardar en el log de la base de datos
                    EmailLog::create([
                        'sender_email' => $message->getFrom()[0]->mail,
                        'received_at' => $receivedDate, // Guardar la fecha de recepción real
                        'file_name' => $attachment->name,
                        'file_size' => $attachment->getSize(),
                    ]);

                    Log::info('Correo con adjunto .json guardado en la base de datos.');
                } else {
                    Log::info('El adjunto no es un archivo .json.');
                }
            }
            // } else {
            //     Log::info('El mensaje no está dentro de los últimos 10 minutos.');
            // }
        }

        $client->disconnect();
        Log::info('Desconexión del servidor IMAP completada.');
    }
}
