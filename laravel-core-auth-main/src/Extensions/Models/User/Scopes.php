<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models\User;

/**
 * Trait Scopes
 *
 * @version 1.0.0
 * @date    2019-02-18
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait Scopes
{

    /**
     * Get user by email
     *
     * @param $query
     * @param $email
     */
    public function scopeByEmail($query, $email)
    {
        $query->where('email', $email);
    }


}