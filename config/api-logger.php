<?php

return [
    /**
     * Log requests
     *
     */
    'enabled' => env('API_LOGGER_ENABLE', true),

    /**
     * Application name
     *
     */
    'application' => env('APP_NAME'),

    /**
     * Elastic Search Configuration
     *
     */
    'general' => [
        'index_name' => env('API_LOGGER_INDEX_NAME'),
        'cloud_id' => env('API_LOGGER_CLOUD_ID'),
        'api_key' => env('API_LOGGER_KEY'),
    ],

    /**
     * Filter out fields which will never be logged
     *
     */
    'except' => [
        'password',
        'password_confirmation'
    ]
];
