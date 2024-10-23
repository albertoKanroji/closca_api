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
            $folders = $client->getFolders();

            foreach ($folders as $folder) {
                $this->info($folder->name . "\n") ;
            }
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

            // Obtener todos los correos no leídos del día actual
            try {
                $client->connect();
                $this->info('Conexión establecida correctamente.');

                // Obtener la bandeja de entrada
                $folder = $client->getFolder('INBOX');
                $this->info('Carpeta INBOX obtenida correctamente.');

                $this->info('Revisando correos no leídos...');

                // Procesar los correos no leídos en lotes (por ejemplo, 50 correos por lote)
             //   $page = 1;
                $perPage = 50;
                do {
                    $messages = $folder->messages()->unseen()->paginate($perPage);

                    $this->info('Procesando lote de ' . count($messages) . ' mensajes.');

                    foreach ($messages as $message) {
                        // Verificar si el mensaje tiene un adjunto .json
                        $hasJsonAttachment = false;

                        foreach ($message->getAttachments() as $attachment) {
                            if (strtolower($attachment->getExtension()) == 'json') {
                                $hasJsonAttachment = true;
                                $this->info('Correo con archivo JSON encontrado: ' . $message->getSubject());
                            }
                        }

                        // Solo procesar los correos que tengan adjuntos .json
                        if ($hasJsonAttachment) {
                            // Procesar el correo, por ejemplo moverlo a otra carpeta
                            try {
                                $jsonFolder = $client->getFolder('JSON RECIBIDOS');
                                $message->move($jsonFolder);
                                $this->info('Correo movido a la carpeta JSON RECIBIDOS.');
                            } catch (\Exception $e) {
                                $this->error('Error al mover el correo: ' . $e->getMessage());
                            }
                        } else {
                            $this->info('Correo sin archivo JSON ignorado: ' . $message->getSubject());
                        }
                    }

                   // $page++;
                } while ($messages->count() > 0); // Continuar mientras haya mensajes
            } catch (\Exception $e) {
                $this->error('Error al conectar al cliente IMAP: ' . $e->getMessage());
            }

            // Si hay correos no leídos, mostrar la barra de progreso
            if (count($messages) > 0) {
                $this->info('Procesando correos...');

                // Usar withProgressBar para mostrar una barra de progreso
                $this->output->progressStart(count($messages));

                foreach ($messages as $message) {
                    // Actualizar la barra de progreso en cada iteración
                    $this->output->progressAdvance();

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

                // Terminar la barra de progreso
                $this->output->progressFinish();
            } else {
                $this->info('No hay correos no leídos del día actual.');
            }

            $this->info('Esperando 10 segundos antes de revisar nuevamente...');
            sleep(10);
        }
    }
}
