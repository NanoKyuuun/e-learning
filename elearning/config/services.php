<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Face Recognition Python API
    |--------------------------------------------------------------------------
    */
    'face_api' => [
        'url'     => env('FACE_API_URL', 'http://127.0.0.1:5000'),
        'key'     => env('FACE_API_KEY'),
        'timeout' => (int) env('FACE_API_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Python AI Service
    |--------------------------------------------------------------------------
    */
    'ai_service' => [
        'url'                 => env('AI_SERVICE_URL', 'http://127.0.0.1:8000'),
        'key'                 => env('AI_SERVICE_API_KEY'),
        'timeout'             => (int) env('AI_SERVICE_TIMEOUT', 90),
        'web_search_enabled'  => (bool) env('AI_ENABLE_WEB_SEARCH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter
    |--------------------------------------------------------------------------
    */
    'openrouter' => [
        'base_url'   => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'api_key'    => env('OPENROUTER_API_KEY'),
        'model'      => env('OPENROUTER_MODEL', 'openrouter/auto'),
        'http_referer'=> env('OPENROUTER_HTTP_REFERER'),
        'app_title'  => env('OPENROUTER_APP_TITLE', 'E-Learning AI Assistant'),
        'web_search' => [
            'mode'              => env('AI_WEB_SEARCH_MODE', 'openrouter_server_tool'),
            'engine'            => env('AI_WEB_SEARCH_ENGINE', 'auto'),
            'max_results'       => (int) env('AI_WEB_SEARCH_MAX_RESULTS', 5),
            'max_total_results' => (int) env('AI_WEB_SEARCH_MAX_TOTAL_RESULTS', 10),
            'context_size'      => env('AI_WEB_SEARCH_CONTEXT_SIZE', 'medium'),
            'fallback_provider' => env('AI_WEB_SEARCH_FALLBACK_PROVIDER', 'duckduckgo'),
        ],
    ],

];

