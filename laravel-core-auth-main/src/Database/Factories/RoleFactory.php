<?php

namespace AttractCores\LaravelCoreAuth\Database\Factories;

use AttractCores\LaravelCoreAuth\Models\Role;

/**
 * Class RoleFactory
 *
 * @package AttractCores\LaravelCoreAuth\Factories
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class RoleFactory extends PermissionFactory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

}