<?php

namespace AttractCores\LaravelCoreAuth;

use \Illuminate\Contracts\Auth\Access\Gate;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use \Illuminate\Foundation\Support\Providers\AuthServiceProvider;

/**
 * Class InitializeCoreRightServiceProvider
 *
 * @package AttractCores\LaravelCoreAuth
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class InitializeCoreRightsServiceProvider extends AuthServiceProvider
{

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     *
     * @return void
     */
    public function boot(Gate $gate)
    {
        $this->registerPolicies();
        $this->initRights($gate);
    }

    /**
     * Initialise Gate contracts.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     */
    protected function initRights(Gate $gate)
    {
        /** @var CorePermissionContract $permission */
        foreach ( app('kit-auth.permissions') as $permission ) {
            $gate->define($permission->slug, function (CoreUserContract $user, $isForce = false) use ($permission) {
                if ( $isForce || ! $permission->canBeOverwritten() ) {
                    $result = $user->hasPermissions($permission);
                } else {
                    $result = $user->hasPermissions($permission) || $user->isValidAdmin();
                }

                return $result;
            });
        }
    }

}