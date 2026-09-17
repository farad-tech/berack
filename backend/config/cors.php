<?php

return [
    'paths' => [
        'save-tracker',
    ],

    'allowed_methods' => [
        'POST',
        'OPTIONS',
    ],

    'allowed_origins' => [
        '*',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
