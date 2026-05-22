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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'plex_crm' => [
        'base_url'       => env('PLEX_CRM_BASE_URL', 'https://app.plex-crm.ru/api/v3'),
        'token'          => env('PLEX_CRM_TOKEN'),
        'timeout'        => (int) env('PLEX_CRM_TIMEOUT', 10),
        'retry_times'    => (int) env('PLEX_CRM_RETRY_TIMES', 3),
        'retry_sleep_ms' => (int) env('PLEX_CRM_RETRY_SLEEP_MS', 1000),
    ],

];
