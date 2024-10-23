<?php
namespace App\Console\Commands;

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
        // Conectarse al cliente IMAP
        $client = Client::account('default');
        $client->connect();

        // Obtener la bandeja de entrada
        $folder = $client->getFolder('INBOX');

        $this->info('Escuchando correos en INBOX...');

        while (true) {
            // Obtener todos los correos no leídos
            $messages = $folder->query()->unseen()->get();

            foreach ($messages as $message) {
                // Verificar si tiene un archivo adjunto .json
                foreach ($message->getAttachments() as $attachment) {
                    if (strtolower($attachment->getExtension()) == 'json') {
                        Log::info('Nuevo correo con archivo JSON recibido: ' . $message->getSubject());

                        // Mover el correo a la carpeta "JSON RECIBIDOS"
                        $jsonFolder = $client->getFolder('JSON RECIBIDOS');
                        $message->move($jsonFolder);

                        $this->info('Correo movido a la carpeta JSON RECIBIDOS');
                    }
                }
            }

            // Pausa de 10 segundos antes de volver a revisar
            sleep(10);
        }
    }
}
