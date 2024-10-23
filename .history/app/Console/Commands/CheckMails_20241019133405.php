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

            // Obtener todos los correos no leídos del día actual
            try {
                // Usar Carbon para obtener la fecha de hoy en el formato d-M-Y
              //  $today = Carbon::today()->format('d-M-Y'); // Ejemplo: 19-Oct-2024
                $last24Hours = Carbon::now()->subDay();
                $tenMinutesAgo = Carbon::now()->subMinutes(10);
                // Filtrar correos recibidos en la fecha actual
                $messages = $folder->messages()->unseen()->since($tenMinutesAgo)->get();
                $this->info(string: count($messages) . ' correos no leídos encontrados para el día de hoy.');
            } catch (\Exception $e) {
                $this->error('Error al obtener correos no leídos: ' . $e);
                return;
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
