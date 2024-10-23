<?php
return [
    'default' => [
        'host'          => env('IMAP_HOST'),
        'port'          => env('IMAP_PORT'),
        'encryption'    => env('IMAP_ENCRYPTION'),
        'validate_cert' => env('IMAP_VALIDATE_CERT', true),
        'username'      => env('IMAP_USERNAME'),
        'password'      => env('IMAP_PASSWORD'),
        'protocol'      => 'imap'
    ]
];
