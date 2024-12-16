<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Life time of access tokens
    |--------------------------------------------------------------------------
    |
    | All values in seconds.
    |
    */
    'life_time'                 => [
        'access_token'  => env('APP_KIT_AUTH_ACCESS_TOKEN_LIFETIME', 24 * 60 * 60),
        'refresh_token' => env('APP_KIT_AUTH_REFRESH_TOKEN_LIFETIME', 14 * 24 * 60 * 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable mobile short codes
    |--------------------------------------------------------------------------
    |
    | Enable or disable mobile short codes.
    |
    */
    'enable_mobile_short_codes' => env('APP_KIT_AUTH_ENABLE_MOBILE_SHORT_CODES', false),

    /*
    |--------------------------------------------------------------------------
    | Password Grant type credentials
    |--------------------------------------------------------------------------
    |
    | Credentials for password grant type token.
    |
    */

    'password_grant' => [
        'id'     => env('APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_ID', NULL),
        'secret' => env('APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_SECRET', NULL),
    ],

    /*
    |--------------------------------------------------------------------------
    | Should Dispatch Registered Event
    |--------------------------------------------------------------------------
    |
    | Enable or disable registered event dispatching.
    |
    */

    'should_dispatch_registered_event' => env('APP_KIT_AUTH_REGISTERED_EVENT_DISPATCHING', true),

    /*
    |--------------------------------------------------------------------------
    | Start user
    |--------------------------------------------------------------------------
    |
    | Start user information. This variable needed for db migrate and create first user.
    | Variable used in login tests. Be careful.
    |
    */

    'start-user'      => [
        'email'    => env('APP_KIT_AUTH_START_USER_EMAIL', 'admin@attractgroup.com'),
        'password' => env('APP_KIT_AUTH_START_USER_PASSWORD', '11111111'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Config for passport extensions
    |--------------------------------------------------------------------------
    |
    | Here you may specify which environments and route patterns which may
    | control different passport grant types. If array is empty - all grant types appears on every requests.
    | If grant type is not exists in array - it will never appears. You * wildcard to define permanent grant type.
    | Possible keys: refresh-token-grant, client-credentials-grant, password-grant, personal-access-grant, implicit-grant(Passport::$implicitGrantEnabled used for test too), auth-code-grant
    |
    */
    'passport-scopes' => [
        'api'     => 'Api Access',
        'backend' => 'Backend Api Access',
    ],

    'passport' => [
        'client-credentials-grant' => [
            'routes'   => [
                'api/*',
            ],
            'app_envs' => [ env('APP_ENV') ],
        ],
        'refresh-token-grant'      => [
            'routes'   => [
                '*/refresh-token',
            ],
            'app_envs' => [ 'local', 'dev', 'testing' ],
        ],
        'password-grant'           => [
            'routes'   => [
                '*/login',
                '*/register',
            ],
            'app_envs' => [ 'local', 'dev', 'testing' ],
        ],
        'personal-access-grant'    => [
            'routes'   => [
                'api/*/auth/*',
            ],
            'app_envs' => [ 'local', 'dev', 'testing' ],
        ],
        'implicit-grant'           => [
            'routes'   => [ '*' ],
            'app_envs' => [ '*' ],
        ],
    ],
];
