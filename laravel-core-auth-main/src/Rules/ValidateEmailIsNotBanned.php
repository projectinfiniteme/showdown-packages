<?php

namespace AttractCores\LaravelCoreAuth\Rules;

use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRole;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Contracts\Validation\Rule;

class ValidateEmailIsNotBanned implements Rule
{

    /**
     * @var bool
     */
    protected $nullable;

    /**
     * @var array
     */
    protected $givenScopes;

    /**
     * Determine that user was trashed.
     *
     * @var boolean
     */
    protected $trashed = false;

    /**
     * Create a new rule instance.
     *
     * @param         $givenScopes
     * @param bool    $nullable
     */
    public function __construct($givenScopes, $nullable = false)
    {
        $this->nullable = $nullable;
        $this->givenScopes = is_array($givenScopes) ? $givenScopes : [];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = CoreUser::withTrashed()->byEmail($value)->with('permissions')->first();

        if ( $this->nullable && ! $user ) {
            return true;
        }

        if ( $user ) {
            // Check that user soft deleted.
            if ( ! is_null($user->deleted_at) ) {
                $this->trashed = true;

                return false;
            }

            // Check other permissions.
            foreach ( $this->givenScopes as $scope ) {
                if ( ! $this->checkScope($scope, $user) ) {
                    // Some checks are broken.
                    return false;
                }
            }

            // All right checked and we good to go.
            return true;
        }

        return false;
    }

    /**
     * Check given scope for login.
     *
     * @param                  $scope
     * @param CoreUserContract $user
     *
     * @return bool
     */
    protected function checkScope($scope, CoreUserContract $user)
    {
        $class = app(CorePermissionContract::class);

        switch ( $scope ) {
            case 'api':
                return $user->can($class::CAN_SIGN_IN);
                break;
            case 'backend':
                return $user->can($class::CAN_BACKEND_SIGN_IN);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ( $this->trashed ) {
            return __("The account has been deleted. Please register again with another email.");
        }

        return __("You don't have enough permissions to login.");
    }

}
