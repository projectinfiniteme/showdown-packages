<?php

namespace App\Postman;

use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;

/**
 * Class ApiLoginRequestFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ApiLoginRequestFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = 'api.auth.login';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        return [
            'email'          => config('kit-auth.start-user.email'),
            'password'       => config('kit-auth.start-user.password'),
            'scopes'         => array_keys(config('kit-auth.passport-scopes')),
            'firebase_token' => NULL,
        ];
    }

}