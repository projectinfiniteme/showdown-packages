<?php

namespace App\Postman;

use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;

/**
 * Class OauthAccessTokenFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class OauthAccessTokenFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = 'api.oauth.passport.token';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        return [
            'how_to_use'         => 'Select grant type and replace json root with given object.',
            'client_credentials' => [
                'grant_type'    => "client_credentials",
                'client_id'     => config('postman.auth_clients.client_credentials.id'),
                'client_secret' => config('postman.auth_clients.client_credentials.secret'),
                'scope'        => [ 'api' ],
            ],
            'password'           => [
                'grant_type'    => "client_credentials",
                'client_id'     => config('postman.auth_clients.password.id'),
                'client_secret' => config('postman.auth_clients.password.secret'),
                'username'      => config('postman.start-user.email'),
                'password'      => config('postman.start-user.password'),
                'scope'        => array_keys(config('kit-auth.passport-scopes')),
            ],
        ];
    }

}