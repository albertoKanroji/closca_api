<?php
return [

    /*
    |--------------------------------------------------------------------------
    | IMAP configuration
    |--------------------------------------------------------------------------
    | Default IMAP Server configuration
    |
    */

    'default' => env('IMAP_DEFAULT_ACCOUNT', 'default'),

    'accounts' => [
        'default' => [
            'host'          => env('IMAP_HOST', 'imap.example.com'),
            'port'          => env('IMAP_PORT', 993),
            'encryption'    => env('IMAP_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username'      => env('IMAP_USERNAME'),
            'password'      => env('IMAP_PASSWORD'),
            'protocol'      => 'imap',
            'options'       => [
                'timeout' => 600 // Tiempo de espera en segundos (10 minutos)
            ]
        ],
    ],

    'options' => [
        'delimiter' => '/',
        'fetch' => FT_UID,
        'fetch_body' => true,
        'fetch_flags' => true,
        'message_key' => 'list',
        'fetch_order' => 'desc',
        'dispositions' => ['attachment', 'inline'],
        'common_folders' => [
            'root' => false,
            'junk' => 'Junk',
            'draft' => 'Drafts',
            'sent' => 'Sent',
            'trash' => 'Trash',
            'archive' => 'Archive',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available date formats
    |--------------------------------------------------------------------------
    */
    // 'date_format' => 'd-M-Y H:i:s O',
];
