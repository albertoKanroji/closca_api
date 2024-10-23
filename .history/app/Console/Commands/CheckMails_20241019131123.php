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
    $this->info('Iniciando proceso de conexión al cliente IMAP...');

    // Conectarse al cliente IMAP
    $client = Client::account('default');
    $this->info('Cliente IMAP creado, intentando conectar...');

    try {
        $client->connect();
        $this->info('Conexión establecida correctamente.');
    } catch (\Exception $e) {
        $this->error('Error al conectar al cliente IMAP: ' . $e->getMessage());
        return;
    }

    // Obtener la bandeja de entrada
    try {
        $folder = $client->getFolder('INBOX');
        $this->info('Carpeta INBOX obtenida correctamente.');
    } catch (\Exception $e) {
        $this->error('Error al obtener la carpeta INBOX: ' . $e->getMessage());
        return;
    }

    $this->info('Escuchando correos en INBOX...');

    // Ciclo infinito para seguir revisando los correos
    while (true) {
        $this->info('Revisando correos no leídos...');

        // Obtener todos los correos no leídos
        try {
            $messages = $folder->query()->unseen()->get();
            $this->info(count($messages) . ' correos no leídos encontrados.');
        } catch (\Exception $e) {
            $this->error('Error al obtener correos no leídos: ' . $e->getMessage());
            continue;
        }

        foreach ($messages as $message) {
            $this->info('Procesando correo: ' . $message->getSubject());

            // Verificar si tiene un archivo adjunto .json
            try {
                foreach ($message->getAttachments() as $attachment) {
                    $this->info('Adjunto encontrado: ' . $attachment->getName());

                    if (strtolower($attachment->getExtension()) == 'json') {
                        Log::info('Nuevo correo con archivo JSON recibido: ' . $message->getSubject());

                        // Mover el correo a la carpeta "JSON RECIBIDOS"
                        try {
                            $jsonFolder = $client->getFolder('JSON RECIBIDOS');
                            $message->move($jsonFolder);
                            $this->info('Correo movido a la carpeta JSON RECIBIDOS.');
                        } catch (\Exception $e) {
                            $this->error('Error al mover el correo a la carpeta JSON RECIBIDOS: ' . $e->getMessage());
                        }
                    } else {
                        $this->info('El archivo adjunto no es un JSON.');
                    }
                }
            } catch (\Exception $e) {
                $this->error('Error al procesar los adjuntos: ' . $e->getMessage());
            }
        }

        $this->info('Esperando 10 segundos antes de revisar nuevamente...');
        sleep(10);
    }
}

}
