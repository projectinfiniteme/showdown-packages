<?php

namespace AttractCores\LaravelCoreAuth\Resolvers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface CoreRoleContract
 *
 * @property string     slug        - Slug of the role.
 * @property string     name_en     - English name of the role.
 * @property Carbon     created_at  - Created at of the model.
 * @property Carbon     updated_at  - Updated at of the model.
 *
 * @property Collection permissions - permissions array.
 *
 * @package AttractCores\LaravelCoreAuth\Resolvers
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface CoreRoleContract
{
    /**
     * Predefined Roles.
     *
     * @var array
     */
    const CAN_SIGN_IN         = 'sign-in';

    const CAN_BACKEND_SIGN_IN = 'backend-sign-in';

    const CAN_ADMIN           = 'admin';

    const CAN_OPERATOR        = 'operator';

    const CAN_USER            = 'user';

    /**
     * Protected Roles.
     *
     * @var array
     */
    const PROTECTED_KEYS = [
        self::CAN_SIGN_IN,
        self::CAN_BACKEND_SIGN_IN,
        self::CAN_ADMIN,
        self::CAN_OPERATOR,
        self::CAN_USER,
    ];

}