<?php

namespace AttractCores\LaravelCoreTestBench;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait UserClassResolver
 *
 * @package AttractCores\LaravelCoreTestBench
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait UserClassResolver
{

    /**
     * Resolve user class
     *
     * @return Model
     */
    abstract public function resolveUser(): Model;

    /**
     * Resolve user class
     *
     * @return Factory
     */
    abstract public function resolveUserFactory(): Factory;

}