<?php

namespace App\Postman;

use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;

/**
 * Class OauthRefreshTokenFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class OauthRefreshTokenFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = 'api.oauth.passport.refresh-token';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        return [
            'refresh_token'  => '{REFRESH_TOKEN_RECEIVED_WITH_OLD_ACCESS_TOKEN}',
            'firebase_token' => NULL,
        ];
    }

}