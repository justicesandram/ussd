<?php

use TNM\USSD\Screens\Welcome;

return [
    'session' => [
        'last_activity_minutes' => 2,
    ],
    'storage' => [
        'driver' => env('USSD_STORAGE_DRIVER', 'cache'),
        'cache_store' => env('USSD_CACHE_STORE', 'default'), // cache store to use when driver is 'cache'
    ],
    'routing' => [
        'prefix' => 'api/ussd',
        'middleware' => ['api'],
        'landing_screen' => Welcome::class
    ],
    'navigation' => [
        'home' => '*',
        'previous' => '#'
    ],
    'default' => [
        'options' => ['Subscribe', 'Unsubscribe', 'Help'],
        'welcome' => 'Welcome to the USSD App',
    ]
];
