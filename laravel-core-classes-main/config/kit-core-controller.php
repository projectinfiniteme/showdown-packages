<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User in response enabled
    |--------------------------------------------------------------------------
    |
    | Determine that user in response enabled.
    |
    */

    'user_in_response' => env('KIT_CORE_RESPONSE_USER_INCLUDED', false),

    /*
    |--------------------------------------------------------------------------
    | Bearer token in response enabled
    |--------------------------------------------------------------------------
    |
    | Determine that bearer token in response enabled.
    |
    */

    'bearer_token_in_response' => env('KIT_CORE_RESPONSE_BEARER_INCLUDED', false),


    /*
    |--------------------------------------------------------------------------
    | User resource
    |--------------------------------------------------------------------------
    |
    | Return user in response resource class name.
    |
    */

    'user_response_resource' => env('KIT_CORE_RESPONSE_RESOURCE', NULL),
];