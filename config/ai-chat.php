<?php

declare(strict_types=1);

return [
    'enabled' => true,

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
    ],
];
