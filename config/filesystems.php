<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'source' => [
            'driver' => 'local',
            'root' => env('SOURCE_DIR')
        ],

        'torrents' => [
            'driver' => 'local',
            'root' => env('TORRENT_DIR')
        ],

        'destination' => [
            'driver' => 's3',
            'key' => 'LRTRHEKPWGMOAP9XGR5K',
            'secret' => 'tU3ZFKKZx6cTnaGW3r0Pa0Vs1LputKng4NwtllN9',
            'region' => 'us-east-1',
            'bucket' => 'vids',
            'endpoint' => env('MINIO_ENDPOINT', 'http://192.168.1.70:9000'),
            'use_path_style_endpoint' => true,
        ],

        // 'destination' => [
        //     'driver' => 'local',
        //     'root' => storage_path('app'),
        // ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

    ],

];
