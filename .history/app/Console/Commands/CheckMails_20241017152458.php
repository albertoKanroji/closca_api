<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog; // Importar el modelo

class CheckMails extends Command
{
    protected $signature = 'mails:check';
    protected $description = 'Check emails and alert on JSON attachment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $client = Client::account('default');
        $client->connect();

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->unseen()->get();

        foreach ($messages as $message) {
            foreach ($message->getAttachments() as $attachment) {
                if ($attachment->getExtension() == 'json') {
                    // Guardar en el log de la base de datos
                    EmailLog::create([
                        'sender_email' => $message->getFrom()[0]->mail,
                        'received_at' => now(), // Puedes obtener la fecha real si está disponible
                        'file_name' => $attachment->name,
                        'file_size' => $attachment->getSize(),
                    ]);

                    Log::info('JSON file detected in email from: ' . $message->getFrom()[0]->mail);
                    // Aquí puedes enviar la alerta o tomar cualquier acción adicional
                }
            }
        }

        $client->disconnect();
    }
}
