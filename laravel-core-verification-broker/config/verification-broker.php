<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Mobile short codes default format.
    |--------------------------------------------------------------------------
    |
    | Should be compatible with \ShortCode\Code::class.
    |
    */

    'mobile_format' => \ShortCode\Code::FORMAT_ALNUM,

    /*
    |--------------------------------------------------------------------------
    | Configure tokens length
    |--------------------------------------------------------------------------
    |
    | Mobile - short codes. Can't be less then 6 and greater then 20.
    | Web - long codes. Length of base part of base 64 hash.
    |
    */

    'length' => [
        'mobile' => env('APP_VERIFICATION_BROKER_MOBILE_CODE_LENGTH', 7),
        'web' => env('APP_VERIFICATION_BROKER_WEB_CODE_LENGTH', 40)
    ],

    /*
    |--------------------------------------------------------------------------
    | Tokens expiration
    |--------------------------------------------------------------------------
    |
    | expires - expiration time in minutes.
    | throttle - time between possible token requests in seconds.
    |
    */

    'lifetime' => [
        'expires' => env('APP_VERIFICATION_BROKER_EXPIRATION', 60),
        'throttle'    => env('APP_VERIFICATION_BROKER_THROTTLE', 60),
    ],
];