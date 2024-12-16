<?php

namespace AttractCores\LaravelCoreAuth\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Interface RegistrationContract
 *
 * @package AttractCores\LaravelCoreAuth\Contracts
 * Date: 15.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface RegistrationContract
{
    /**
     * Register new user.
     *
     * @param array $validated
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function register(array $validated) : Authenticatable;
}