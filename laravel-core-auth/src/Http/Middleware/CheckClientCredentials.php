<?php

namespace AttractCores\LaravelCoreAuth\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\Http\Middleware\CheckClientCredentials as CoreCheckClientCredentials;

/**
 * Class CheckClientCredentials
 *
 * @version 1.0.0
 * @date    21/01/2020
 * @author  Yure Nery <yurenery@gmail.com>
 */
class CheckClientCredentials extends CoreCheckClientCredentials
{

    /**
     * Validate token credentials.
     *
     * @param \Laravel\Passport\Token $token
     *
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function validateCredentials($token)
    {
        if ( ! $token || $token->client->personal_access_client) {
            throw new AuthenticationException;
        }
    }
}
