<?php

namespace AttractCores\LaravelCoreAuth\Resolvers;

use Illuminate\Support\Facades\Facade;

/**
 * Class CoreRole
 *
 * @package AttractCores\LaravelCoreAuth\Resolvers
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreRole extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return CoreRoleContract::class;
    }
}