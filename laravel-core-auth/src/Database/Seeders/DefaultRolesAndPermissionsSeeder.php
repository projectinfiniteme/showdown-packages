<?php

namespace AttractCores\LaravelCoreAuth\Database\Seeders;

use AttractCores\LaravelCoreAuth\Resolvers\CorePermission;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRole;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use \AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use Illuminate\Database\Seeder;

/**
 * Class DefaultRolesAndPermissionsSeeder
 *
 * @package AttractCores\LaravelCoreAuth\Database\Seeders
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class DefaultRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $permissions = CorePermission::all();
        $permissionSlugFieldName = CorePermission::getSlugField();

        //!!!! DO NOT MOVE THE ORDER.
        //Permissions.
        if ( ! $permissions->contains($permissionSlugFieldName, CorePermissionContract::CAN_SIGN_IN) ) {
            CorePermission::factory()
                          ->createOne([ 'name_en' => 'Can sign-in into client side', 'slug' => CorePermissionContract::CAN_SIGN_IN ]);

            CoreRole::factory()
                    ->createOne([ 'name_en' => 'Can sign-in into client side', 'slug' => CoreRoleContract::CAN_SIGN_IN ])
                    ->permissions()
                    ->sync([ 1 ]);
        }

        if ( ! $permissions->contains($permissionSlugFieldName, CorePermissionContract::CAN_BACKEND_SIGN_IN) ) {
            CorePermission::factory()
                          ->createOne([ 'name_en' => 'Can sign-in into Admin panel', 'slug' => CorePermissionContract::CAN_BACKEND_SIGN_IN ]);

            CoreRole::factory()
                    ->createOne([ 'name_en' => 'Can sign-in into Admin panel', 'slug' => CoreRoleContract::CAN_BACKEND_SIGN_IN ])
                    ->permissions()
                    ->sync([ 2 ]);
        }

        if ( ! $permissions->contains($permissionSlugFieldName, CorePermissionContract::CAN_USER_ACCESS) ) {
            CorePermission::factory()
                          ->createOne([ 'name_en' => 'Can have user access', 'slug' => CorePermissionContract::CAN_USER_ACCESS ]);

            CoreRole::factory()->createOne([ 'name_en' => 'User Access', 'slug' => CoreRoleContract::CAN_USER ])
                    ->permissions()->sync([ 3 ]);
        }

        if ( ! $permissions->contains($permissionSlugFieldName, CorePermissionContract::CAN_ADMIN_ACCESS) ) {
            CorePermission::factory()
                          ->createOne([ 'name_en' => 'Can have super admin access', 'slug' => CorePermissionContract::CAN_ADMIN_ACCESS ]);

            CoreRole::factory()->createOne([ 'name_en' => 'Super Admin access', 'slug' => CoreRoleContract::CAN_ADMIN ])
                    ->permissions()->sync([ 4 ]);
        }

        if ( ! $permissions->contains($permissionSlugFieldName, CorePermissionContract::CAN_ADMIN_ACCESS) ) {
            CorePermission::factory()
                          ->createOne([ 'name_en' => 'Can have manager access into Admin panel', 'slug' => CorePermissionContract::CAN_OPERATOR_ACCESS ]);

            CoreRole::factory()->createOne([ 'name_en' => 'Manager Access', 'slug' => CoreRoleContract::CAN_OPERATOR ])
                    ->permissions()->sync([ 5 ]);
        }


        // Update main admin.
        if ( $user = CoreUser::byEmail(config('kit-auth.start-user.email'))->first() ) {
            $user->actAs([
                CoreRoleContract::CAN_SIGN_IN, CoreRoleContract::CAN_BACKEND_SIGN_IN, CoreRoleContract::CAN_ADMIN,
            ]);
        }
    }

}