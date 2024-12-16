<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models\User;

use AttractCores\LaravelCoreAuth\Resolvers\CorePermission;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRole;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait for user roles.
 *
 * @version 1.0.0
 * @date    03/06/2016
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait HasRoles
{

    /**
     * Add role to user model.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function actAs(...$roles)
    {
        $roles = $this->getRolesForActOrDeAct($roles);

        $result = $this->roles()->syncWithoutDetaching($roles->pluck('id'));

        if ( ! empty($result[ 'attached' ]) || ! empty($result[ 'detached' ]) ) {
            $this->loadRolesRelation();
        }

        return $result;
    }

    /**
     * Add role to user model.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function forceActAs(...$roles)
    {
        $roles = $this->getRolesForActOrDeAct($roles);

        $result = $this->roles()->sync($roles->pluck('id'));

        if ( ! empty($result[ 'attached' ]) || ! empty($result[ 'detached' ]) ) {
            $this->loadRolesRelation();
        }

        return $result;
    }

    /**
     * @param array $roles
     *
     * @return array|\Illuminate\Support\Collection|mixed
     */
    protected function getRolesForActOrDeAct(array $roles)
    {
        //if we receive array variable.
        if ( is_array($roles[ 0 ]) && count($roles) == 1 ) {
            $roles = $roles[ 0 ];
        }

        $roleNames = collect($roles)->map(function ($item) {
            if ( $item instanceof CoreRoleContract ) {
                return $item->{CoreRole::getSlugField()};
            }

            return $item;
        })->all();

        $roles = CoreRole::whereIn(CoreRole::getSlugField(), $roleNames)->get();

        if ( empty($roles) ) {
            throw ( new ModelNotFoundException )->setModel(get_class(app(CoreRoleContract::class)));
        }

        return $roles;
    }

    /**
     * Get roles of current user model.
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(get_class(app(CoreRoleContract::class)), 'users_roles');
    }

    /**
     * Reload roles relation.
     */
    public function loadRolesRelation()
    {
        $this->load([ 'roles.permissions' ]);
    }

    /**
     * Detach role form user model.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function deActAs(...$roles)
    {
        $roles = $this->getRolesForActOrDeAct($roles);

        $result = $this->roles()->detach($roles);

        if ( $result ) {
            $this->loadRolesRelation();
        }

        return $result;
    }

    /**
     * Check is user has a role.
     *
     * @param $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if ( is_string($role) ) {
            return $this->roles->contains(CoreRole::getSlugField(), $role);
        }

        return ! ! $role->intersect($this->roles)->count();
    }

    /**
     * Check is user has a role.
     *
     * @param mixed ...$permissions
     *
     * @return bool
     */
    public function hasPermissions(...$permissions)
    {
        // Normalize start array.
        if ( is_array($permissions[ 0 ]) ) {
            $permissions = $permissions[ 0 ];
        }

        // Check if we provide single check on core class object.
        $corePermission = get_class(app(CorePermissionContract::class));
        $normalizedPermissions = [];

        foreach ($permissions as $permission){
            if($permission instanceof $corePermission){
                $normalizedPermissions[] = $permission->slug;
            }else{
                $normalizedPermissions[] = $permission;
            }
        }

        // Checking...
        $startCount = $this->permissions->count();
        $subCount = count($normalizedPermissions);

        // Check that if we remove given permissions from current user permissions, count of permissions decrease by count of needed permissions.
        return count(array_diff($this->permissions_codes, $normalizedPermissions)) == ( $startCount - $subCount );
    }

    /**
     * Scope to get users by role
     *
     * @param        $query
     * @param        $roleKey
     * @param bool   $and
     *
     * @param string $operator
     *
     * @return bool
     */
    public function scopeHasRole($query, $roleKey, $and = false, $operator = 'whereHas')
    {
        if ( ! is_array($roleKey) ) {
            $roleKey = [ $roleKey ];
        }

        return $query->$operator('roles', function ($query) use ($roleKey, $and, $operator) {
            $query->bySlug($roleKey)
                  ->havingRaw("COUNT(DISTINCT roles.id) >= ?", [ $and ? count($roleKey) : 1 ]);

            $dbConnection = $query->getConnection()->getName();
            if ( config("database.connections.$dbConnection.strict", true) ) {
                $query->groupBy('roles.id');
            }
        });
    }

    /**
     * Scope to get users by role
     *
     * @param        $query
     * @param        $roleKey
     *
     * @return bool
     */
    public function scopeDoesntHaveRole($query, $roleKey)
    {
        return $query->hasRole($roleKey, false, 'whereDoesntHave');
    }

    /**
     * Scope to get users by role
     *
     * @param        $query
     * @param        $permissionKey
     * @param bool   $and
     *
     * @param string $operator
     *
     * @return bool
     */
    public function scopeHasPermission($query, $permissionKey, $and = false, $operator = 'whereHas')
    {
        if ( ! is_array($permissionKey) ) {
            $permissionKey = [ $permissionKey ];
        }

        return $query->$operator('roles', function (Builder $query) use ($permissionKey, $and, $operator) {
            $query->join('roles_permissions', 'roles.id', 'roles_permissions.role_id')
                  ->join('permissions', 'roles_permissions.permission_id', 'permissions.id')
                  ->bySlug($permissionKey)
                  ->havingRaw("COUNT(DISTINCT permissions.id) >= ?", [ $and ? count($permissionKey) : 1 ]);

            $dbConnection = $query->getConnection()->getName();
            if ( config("database.connections.$dbConnection.strict", true) ) {
                $query->groupBy('permissions.id');
            }
        });
    }

    /**
     * Scope to get users without permissions.
     *
     * @param        $query
     * @param        $permissionKey
     *
     * @return bool
     */
    public function scopeDoesntHavePermission($query, $permissionKey)
    {
        return $query->hasPermission($permissionKey, false, 'whereDoesntHave');
    }

    /**
     * Toggle a specified roles on the user.
     *
     * @param $roles
     *
     * @return mixed
     */
    public function toggleRoles($roles)
    {
        if ( ! is_array($roles) ) {
            $roles = [ $roles ];
        }
        $roles = CoreRole::whereIn(CoreRole::getSlugField(), $roles)->get();

        return $this->roles()->toggle($roles);
    }

    /**
     * Check if current user is valid admin.
     * Not enough to have admin permission only to reassign other permissions.
     * We need to check backend-sign-in permission with admin permission together.
     *
     * @return bool
     */
    public function isValidAdmin()
    {
        return $this->can(CorePermissionContract::CAN_BACKEND_SIGN_IN) &&
               $this->can(CorePermissionContract::CAN_ADMIN_ACCESS);
    }

    /**
     * Determine is current user is operator.
     *
     * @return bool
     */
    public function isOperator()
    {
        return $this->can(CorePermissionContract::CAN_BACKEND_SIGN_IN) &&
               $this->can(CorePermissionContract::CAN_OPERATOR_ACCESS, true);
    }

}
