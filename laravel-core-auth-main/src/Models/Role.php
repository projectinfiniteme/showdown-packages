<?php

namespace AttractCores\LaravelCoreAuth\Models;

use Amondar\Sextant\Models\SextantModel;
use AttractCores\LaravelCoreAuth\Database\Factories\RoleFactory;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

/**
 * Class Role
 *
 * @property string     slug        - Slug of the role.
 * @property string     name_en     - English name of the role.
 * @property Carbon     created_at  - Created at of the model.
 * @property Carbon     updated_at  - Updated at of the model.
 *
 * @property Collection permissions - permissions array.
 *
 * @package App\Models
 */
class Role extends SextantModel implements CoreRoleContract
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name_en', 'slug' ];

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
        return 'roles.slug';
    }

    /**
     * Extra relations
     *
     * @return array
     */
    public function extraFields()
    {
        return [ 'permissions' ];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return RoleFactory::new();
    }

    /**
     * Permissions relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(get_class(app(CorePermissionContract::class)), 'roles_permissions');
    }

    /**
     * Check if model is protected.
     *
     * @return bool
     */
    public function isProtected()
    {
        return in_array($this->slug, self::PROTECTED_KEYS);
    }

    /**
     * Return roles by slug.
     *
     * @param $query
     * @param mixed $slug
     */
    public function scopeBySlug($query, $slug)
    {
        $query->{is_array($slug) ? 'whereIn' : 'where'}(self::getQualifiedSlugField(), $slug);
    }

}
