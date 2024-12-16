<?php

namespace AttractCores\LaravelCoreAuth\Database\Factories;

use AttractCores\LaravelCoreAuth\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class PermissionFactory
 *
 * @package AttractCores\LaravelCoreAuth\Factories
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PermissionFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'slug'       => $this->faker->slug,
            'name_en'    => $this->faker->name,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

}