<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models\User;

/**
 * Trait HasFireBaseToken
 *
 * @version 1.0.0
 * @date    2019-02-22
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait HasFireBaseToken
{

    /**
     * Attach new firebase token to the user.
     *
     * @param $token
     *
     * @return HasFireBaseToken
     */
    public function setFireBaseToken($token)
    {
        if (
            ! empty($token) && (
                ! $this->firebase_token || $this->firebase_token != $token
            )
        ) {
            $this->firebase_token = $token;
            $this->save();
        }

        return $this;
    }
}