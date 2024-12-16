<?php

namespace AttractCores\LaravelCoreAuth\Resolvers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface CorePermissionContract
 *
 * @property string     slug       - Slug of the permission.
 * @property string     name_en    - English name of the permission.
 * @property Carbon     created_at - Created at of the model.
 * @property Carbon     updated_at - Updated at of the model..
 *
 * @property Collection $roles     - roles array.
 *
 * @package AttractCores\LaravelCoreAuth\Resolvers
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface CorePermissionContract
{
    const CANT_BE_OVERWRITTEN = [ 'backend-sign-in', 'sign-in', 'admin' ];

    const CAN_SIGN_IN         = 'sign-in';

    const CAN_BACKEND_SIGN_IN = 'backend-sign-in';

    const CAN_ADMIN_ACCESS    = 'admin';

    // Other users permissions
    const CAN_OPERATOR_ACCESS = 'operator';

    const CAN_USER_ACCESS     = 'user';


    /**
     * Determine that permission can be overwritten.
     *
     * @return bool
     */
    public function canBeOverwritten() : bool;

}