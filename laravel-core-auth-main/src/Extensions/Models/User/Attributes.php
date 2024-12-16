<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models\User;

use AttractCores\LaravelCoreAuth\Models\Role;
use App\Models\UserBalance;
use Illuminate\Support\Str;

/**
 * Trait Attributes
 *
 * @version 1.0.0
 * @date    2019-02-18
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait Attributes
{

    /**
     * Set email in lower case always.
     *
     * @param $email
     */
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = Str::lower($email);
    }

    /**
     * Set password with encryption.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Return permissions code names.
     *
     * @return mixed
     */
    public function getPermissionsCodesAttribute()
    {
        return $this->permissions->pluck('slug')->toArray();
    }

    /**
     * Return permissions code names.
     *
     * @return mixed
     */
    public function getRolesNamesAttribute()
    {
        return $this->roles->implode('name_en', ', ');
    }
}
