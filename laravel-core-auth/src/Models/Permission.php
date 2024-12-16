<?php

namespace AttractCores\LaravelCoreAuth\Models;

use Amondar\Sextant\Models\SextantModel;
use AttractCores\LaravelCoreAuth\Database\Factories\PermissionFactory;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

/**
 * Class Permission
 *
 * @property string     slug       - Slug of the permission.
 * @property string     name_en    - English name of the permission.
 * @property Carbon     created_at - Created at of the model.
 * @property Carbon     updated_at - Updated at of the model..
 *
 * @property Collection $roles     - roles array.
 *
 * @package App\Models
 */
class Permission extends SextantModel implements CorePermissionContract
{

    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return PermissionFactory::new();
    }

    /**
     * Return role field code name.
     *
     * @return string
     */
    public static function getSlugField()
    {
        return 'slug';
    }

    /**
     * Return role field code name.
     *
     * @return string
     */
    public static function getQualifiedSlugField()
    {
        return 'permissions.slug';
    }

    /**
     * Return array of expandable relation for remote api calls
     *
     * @return array
     */
    public function extraFields()
    {
        return [ "roles" ];
    }

    /**
     * Roles where current permission exists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(get_class(app(CoreRoleContract::class)), 'roles_permissions');
    }

    /**
     * Return roles by slug.
     *
     * @param       $query
     * @param mixed $slug
     */
    public function scopeBySlug($query, $slug)
    {
        $query->{is_array($slug) ? 'whereIn' : 'where'}(self::getQualifiedSlugField(), $slug);
    }

    /**
     * Determine that permission can be overwritten.
     *
     * @return bool
     */
    public function canBeOverwritten() : bool
    {
        return ! in_array($this->slug, static::CANT_BE_OVERWRITTEN);
    }

}
