<?php

namespace AttractCores\LaravelCoreAuth\Resolvers;

use Illuminate\Contracts\Auth\Access\Authorizable;

/**
 * Interface UserContract
 *
 * @package AttractCores\LaravelCoreAuth\Resolvers
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface CoreUserContract extends Authorizable
{

    /**
     * FRONTEND side marker, for Auth Microservice checks.
     */
    const FRONTEND_REQUEST_SIDE = 'frontend';

    /**
     * BACKEND side marker, for Auth Microservice checks.
     */
    const BACKEND_REQUEST_SIDE = 'backend';

    /**
     * Add role to user model.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function actAs(...$roles);

    /**
     * Check is user has a role.
     *
     * @param $role
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Check is user has permissions.
     *
     * @param mixed ...$permissions
     *
     * @return bool
     */
    public function hasPermissions(...$permissions);

    /**
     * Check if current user is valid admin.
     * Not enough to have admin permission only to reassign other permissions.
     * We need to check backend-sign-in permission with admin permission together.
     *
     * @return bool
     */
    public function isValidAdmin();
}