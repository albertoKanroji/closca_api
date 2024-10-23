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
        $client = Client::account('default');
        $client->connect();

        $folder = $client->getFolder('INBOX');
        $oneHourAgo = Carbon::now()->subHour(); // Obtener la fecha de una hora atrás

        $messages = $folder->messages()->unseen()->since($oneHourAgo)->get();

        foreach ($messages as $message) {
            $receivedDate = $message->getDate();
            // Verificamos si el mensaje se recibió en la última hora
            if ($receivedDate >= $oneHourAgo) {
                foreach ($message->getAttachments() as $attachment) {
                    if ($attachment->getExtension() == 'json') {
                        // Guardar en el log de la base de datos
                        EmailLog::create([
                            'sender_email' => $message->getFrom()[0]->mail,
                            'received_at' => $receivedDate, // Guardar la fecha de recepción real
                            'file_name' => $attachment->name,
                            'file_size' => $attachment->getSize(),
                        ]);

                        Log::info('JSON file detected in email from: ' . $message->getFrom()[0]->mail);
                    }
                }
            }
        }

        $client->disconnect();
    }
}
