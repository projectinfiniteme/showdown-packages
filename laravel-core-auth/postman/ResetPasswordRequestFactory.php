<?php

namespace App\Postman;

use AttractCores\PostmanDocumentation\Factory\FormRequestFactory;

/**
 * Class ResetPasswordRequestFactory
 *
 * @package App\Postman
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ResetPasswordRequestFactory extends FormRequestFactory
{

    /**
     * The name of the factory's corresponding form request or full route name.
     *
     * @var string|null
     */
    protected ?string $request = 'api.auth.password.reset';

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        $pwd = $this->faker->password(10);

        return [
            'email' => $this->faker->safeEmail,
            'password' => $pwd,
            'password_confirmation' => $pwd,
            'token' => '{TOKEN_OR_CODE_RECEIVED_FROM_EMAIL_SMS_ETC}',
        ];
    }

}