<?php

return [
    'host' => env('TRANSMISSION_HOST', '127.0.0.1'),
    'port' => env('TRANSMISSION_PORT', 9091),
    'username' => env('TRANSMISSION_USERNAME', ''),
    'password' => env('TRANSMISSION_PASSWORD', ''),
    'send_failure_notifications' => env('NOTIFY_FAILURE', false),
    'send_success_notifications' => env('NOTIFY_SUCCESS', false),
    'notification_address' => env('NOTIFICATION_EMAIL', null),
    'max_tries' => 3,
];
