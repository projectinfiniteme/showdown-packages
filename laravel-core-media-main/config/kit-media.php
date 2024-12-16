<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Base Path Prefix
    |--------------------------------------------------------------------------
    |
    | Base Path Prefix.
    |
    */

    'base_path_prefix' => env('APP_KIT_MEDIA_URL_PREFIX', 'storage/'),

    /*
    |--------------------------------------------------------------------------
    | Requests throttle
    |--------------------------------------------------------------------------
    |
    | Throttle of all requests on media routes.
    | Affect "kit-media" rate limiter added by service provider.
    |
    */

    'requests_throttle' => env('APP_KIT_MEDIA_REQUESTS_THROTTLE', 15),

    /*
    |--------------------------------------------------------------------------
    | Resize On Fly
    |--------------------------------------------------------------------------
    |
    | Resize on fly settings.
    |
    */

    'resize_on_fly' => [
        'enabled'     => env('APP_KIT_MEDIA_RESIZE_ON_FLY', false),
        'crop_path'   => env('APP_KIT_MEDIA_CROP_PATH', 'image/crop/storage/'),
        'resize_path' => env('APP_KIT_MEDIA_CROP_PATH', 'image/resize/storage/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | TMP directory path
    |--------------------------------------------------------------------------
    |
    | Default tmp directory path.
    |
    */

    'tmp_path' => env('APP_KIT_MEDIA_TMP_PATH', 'tmp/'),

    /*
    |--------------------------------------------------------------------------
    | Default queue name
    |--------------------------------------------------------------------------
    |
    | Default queue name for async jobs.
    |
    */

    'default_queue' => env('APP_KIT_MEDIA_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Deleted files retention
    |--------------------------------------------------------------------------
    |
    | Retention period in minutes for deleted files
    |
    */

    'deleted_retention' => env('APP_KIT_MEDIA_DELETED_FILES_RETENTION', 2 * 24 * 60),

    /*
    |--------------------------------------------------------------------------
    | Restrictions
    |--------------------------------------------------------------------------
    |
    | Fill files restrictions.
    | max_size - in kilobytes.
    |
    */

    'restrictions' => [
        'max_size'  => env('APP_KIT_MEDIA_FILE_MAX_SIZE', 10000),
        'mimetypes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | File URL life time
    |--------------------------------------------------------------------------
    |
    | File URL life time. IN MINUTES.
    |
    */

    'pre_signed_lifetime' => [
        'sources'   => env('APP_KIT_MEDIA_SOURCES_PRE_SIGNED_LIFETIME', 60),
        'resources' => env('APP_KIT_MEDIA_RESOURCES_PRE_SIGNED_LIFETIME', 60),
        'upload'    => env('APP_KIT_MEDIA_UPLOAD_PRE_SIGNED_LIFETIME', 360),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Routes
    |--------------------------------------------------------------------------
    |
    | Determine which routes should be enabled.
    |
    */

    'routes_availability' => [
        'local'            => env('APP_KIT_MEDIA_ENABLE_LOCAL_ROUTES', true),
        's3-store'         => env('APP_KIT_MEDIA_ENABLE_S3_STORE_ROUTE', true),
        's3-signed-upload' => env('APP_KIT_MEDIA_ENABLE_S3_SIGNED_UPLOAD_ROUTE', true),
        's3-signed-source' => env('APP_KIT_MEDIA_ENABLE_S3_SIGNED_SOURCE_ROUTE', true),
    ],

];
