<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models\User;

use AttractCores\LaravelCoreAuth\Models\Permission;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Trait Relations
 *
 * @property File|NULL avatar - User avatar.
 *
 * @version 1.0.0
 * @date    2019-07-28
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait Relations
{

    use HasRelationships;

    /**
     * Return
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function permissions()
    {
        return $this->hasManyDeep(
            get_class(app(CorePermissionContract::class)),
            [ 'users_roles', get_class(app(CoreRoleContract::class)), 'roles_permissions' ],
            [
                'user_id', // Foreign key on the "users_roles" table.
                'id', // Foreign key on the "roles" table (local key).
                'role_id', // Foreign key on the "roles_permissions" table.
                'id' // Foreign key on the "permissions" table (local key).
            ],
            [
                'id', // Local key on the "users" table.
                'role_id', // Local key on the "users_roles" table (foreign key).
                'id', // Local key on the "roles" table.
                'permission_id' // Local key on the "roles_permissions" table (foreign key).
            ],
        );
    }

}
