<?php

return [
    /**
     * Should reactor send a notification
     *
     */
    'enabled' => env('REACTOR_ENABLED', false),

    /**
     * Where notification will be routed
     *
     */
    'notification_routes' => [
        'mail',
        'teams'
    ],

    /**
     * List of exception that should not be reported
     *
     */
    'dont_report' => [
        Illuminate\Validation\ValidationException::class,
        Illuminate\Auth\AuthenticationException::class,
        Illuminate\Auth\Access\AuthorizationException::class,
        Illuminate\Database\Eloquent\ModelNotFoundException::class,
        Symfony\Component\HttpKernel\Exception\HttpException::class,
        Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

    'teams' => [
        'channel_url' => env('REACTOR_MS_TEAMS_CHANNEL_URL')
    ],

    'mail' => [
        'to' => env('REACTOR_MAIL_DESTINATIONS', 'dev@finxp.com')
    ]
];
