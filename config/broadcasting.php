<?php

return [
    'default' => env('BROADCAST_DRIVER', 'reverb'),

    'connections' => [
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY', 'local'),
            'secret' => env('REVERB_APP_SECRET', 'local'),
            'app_id' => env('REVERB_APP_ID', 'local'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => 'http',
            ],
        ],
    ],
];

