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

        'files' => [
            'driver' => 'local',
            'root' => env('FILE_DIR')
        ],

        'torrents' => [
            'driver' => 'local',
            'root' => env('TORRENT_DIR')
        ],

        'destination' => [
            'driver' => 's3',
            'key' => env("MINIO_KEY"),
            'secret' => env("MINIO_SECERT"),
            'region' => env("MINIO_REGION"),
            'bucket' => env("MINIO_BUCKET"),
            'endpoint' => env('MINIO_ENDPOINT'),
            'use_path_style_endpoint' => true,
        ],

        // 'destination' => [
        //     'driver' => 'local',
        //     'root' => '/tmp/destination',
        // ],
    ],

];
