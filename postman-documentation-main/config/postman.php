<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Collection Filename
    |--------------------------------------------------------------------------
    |
    | The name for the collection file to be saved.
    |
    */

    'filename' => '{timestamp}_{app}_collection.json',

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for all of your endpoints.
    |
    */

    'base_url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Structured
    |--------------------------------------------------------------------------
    |
    | If you want folders to be generated based on namespace.
    |
    */

    'structured' => true,

    /*
    |--------------------------------------------------------------------------
    | Base oAuth route
    |--------------------------------------------------------------------------
    |
    | The base oAuth route for authentication of your endpoints.
    |
    */

    'oauth_route' => env('POSTMAN_OAUTH_ROUTE', 'api.oauth.passport.token'),

    /*
    |--------------------------------------------------------------------------
    | Auth type
    |--------------------------------------------------------------------------
    |
    | The Authentication Type.
    |
    | E.g. oauth2, token, none
    |
    */

    'auth_type' => 'oauth2',

    /*
    |--------------------------------------------------------------------------
    | Token placement
    |--------------------------------------------------------------------------
    |
    | Value of "Add To" postman option.
    |
    | E.g. header, query
    |
    */

    'token_placement' => 'header',

    /*
    |--------------------------------------------------------------------------
    | Auth Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware which wraps your authenticated API routes.
    |
    | E.g. auth:api, auth:sanctum
    |
    */

    'auth_middleware' => 'auth:api',

    /*
    |--------------------------------------------------------------------------
    | Client Auth Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware which wraps your client authenticated API routes.
    |
    | E.g. auth-api-client
    |
    */

    'client_auth_middleware' => 'auth-api-client',

    /*
    |--------------------------------------------------------------------------
    | Auth clients
    |--------------------------------------------------------------------------
    |
    | The client authentication tokens for each request.
    |
    */

    'auth_clients' => [
        'client_credentials' => [
            'id'     => env('POSTMAN_CLIENT_ID'),
            'secret' => env('POSTMAN_CLIENT_SECRET'),
        ],
        'password'           => [
            'id'     => env('APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_ID'),
            'secret' => env('APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | The authentication scopes patterns.
    |
    */

    'scopes' => [
        "/^api\/v1.+/" => 'api',
        "/^backend\/v1.+/" => 'backend',
        "/^api\/auth\/.+/" => 'api backend',
    ],

    /*
    |--------------------------------------------------------------------------
    | Start user
    |--------------------------------------------------------------------------
    |
    | Start user information.
    |
    */

    'start-user'      => [
        'email'    => env('APP_KIT_AUTH_START_USER_EMAIL', 'admin@attractgroup.com'),
        'password' => env('APP_KIT_AUTH_START_USER_PASSWORD', '11111111'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | The headers applied to all routes within the collection.
    |
    */

    'headers' => [
        [
            'key'   => 'Accept',
            'value' => 'application/json',
            'type'  => 'text',
        ],
        [
            'key'   => 'Content-Type',
            'value' => 'application/json',
            'type'  => 'text',
        ],
        [
            'key'   => 'X-Requested-With',
            'value' => 'XMLHttpRequest',
            'type'  => 'text',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Form Data
    |--------------------------------------------------------------------------
    |
    | Determines whether or not form data should be handled.
    |
    */

    'enable_formdata' => true,

    /*
    |--------------------------------------------------------------------------
    | Form Data Factories Path
    |--------------------------------------------------------------------------
    |
    | The path or factories directory.
    |
    */

    'factories_path' => app_path('Postman'),

    /*
    |--------------------------------------------------------------------------
    | Include Middleware
    |--------------------------------------------------------------------------
    |
    | The routes of the included middleware are included in the export.
    |
    */

    'include_middleware' => [ 'api' ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Middleware
    |--------------------------------------------------------------------------
    |
    | The routes of the excluded middleware are excluded from the export, even if including middleware present.
    | Use this parameter to exclude webhooks endpoints.
    |
    */

    'exclude_middleware' => [ ],

    /*
    |--------------------------------------------------------------------------
    | Disk Driver
    |--------------------------------------------------------------------------
    |
    | Specify the configured disk for storing the postman collection file.
    |
    */

    'disk' => 'public',

];
