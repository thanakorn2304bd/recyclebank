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

    'document_storage' => [
        'driver' => env('DOCUMENT_STORAGE_DRIVER', 'local'),
        'local_disk' => env('DOCUMENT_STORAGE_LOCAL_DISK', 'local'),
        'vercel_blob_token' => env('BLOB_READ_WRITE_TOKEN'),
    ],

    'backup_storage' => [
        'driver' => env('BACKUP_STORAGE_DRIVER', env('DOCUMENT_STORAGE_DRIVER', 'local')),
        'local_disk' => env('BACKUP_STORAGE_LOCAL_DISK', 'local'),
        'vercel_blob_token' => env('BLOB_READ_WRITE_TOKEN'),
    ],

];
