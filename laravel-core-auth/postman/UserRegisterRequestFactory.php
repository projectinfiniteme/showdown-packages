<?php

namespace App\Postman;

use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;

/**
 * Class UserRegisterRequestFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class UserRegisterRequestFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = 'api.auth.register';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        return [
            'email'          => $this->faker->safeEmail,
            'password'       => $this->faker->password(10),
            'name'           => $this->faker->name,
            'firebase_token' => NULL,
            '*scopes'        => [ 'api' ],
        ];
    }

}