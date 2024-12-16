<?php

namespace AttractCores\LaravelCoreAuth\Database\Factories;

use AttractCores\LaravelCoreAuth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class UserFactory
 *
 * @package AttractCores\LaravelCoreAuth\Factories
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class UserFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => '11111111',
            'email_verified_at' => now(),
            'terms_accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create not verified user.
     *
     * @return \AttractCores\LaravelCoreAuth\Database\Factories\UserFactory
     */
    public function notVerified()
    {
        return $this->state([
            'email_verified_at' => NULL,
        ]);
    }

    /**
     * Create user with given name.
     *
     * @param string $firstName
     * @param string $lastName
     *
     * @return static
     */
    public function name(string $firstName, string $lastName)
    {
        return $this->state([
            'name' => $firstName . ' ' . $lastName,
        ]);
    }

    /**
     * Create user with admin fields.
     *
     * @return static
     */
    public function admin()
    {
        return $this->state([ ]);
    }

}