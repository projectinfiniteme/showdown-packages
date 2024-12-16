<?php

namespace AttractCores\LaravelCoreAuth\Models;

use Amondar\Sextant\Models\HasSextantOperations;
use AttractCores\LaravelCoreAuth\Database\Factories\UserFactory;
use AttractCores\LaravelCoreAuth\Extensions\Models\QuietlySaveable;
use AttractCores\LaravelCoreAuth\Extensions\Models\User\Attributes;
use AttractCores\LaravelCoreAuth\Extensions\Models\User\HasFireBaseToken;
use AttractCores\LaravelCoreAuth\Extensions\Models\User\HasRoles;
use AttractCores\LaravelCoreAuth\Extensions\Models\User\Relations;
use AttractCores\LaravelCoreAuth\Extensions\Models\User\Scopes;
use AttractCores\LaravelCoreAuth\Notifications\ResetPassword;
use AttractCores\LaravelCoreAuth\Notifications\VerifyEmail;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @property string      email                            - Email of the user.
 * @property string      password                         - Password of the user.
 * @property Carbon|null email_verified_at                - Date of the confirmation.
 * @property Carbon|null terms_accepted_at                - Date of the terms and conditions accept.
 * @property string      name                             - User name.
 * @property string|null firebase_token                   - Fire base token.
 * @property Carbon|null created_at                       - Date of the creation.
 * @property Carbon|null updated_at                       - Date of the update.
 *
 * @property array       permissions_codes                - Array with permissions names.
 * @property string      roles_names                      - Concatenated roles names string.
 *
 *
 * @property Collection  roles                            - Roles list.
 * @property Collection  permissions                      - Permissions list.
 *
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail, CoreUserContract, CanVerifyActions
{

    use Notifiable, HasApiTokens, HasRoles, HasSextantOperations, SoftDeletes,
        Scopes, Attributes, HasFireBaseToken, Relations, QuietlySaveable, HasFactory;

    /**
     * Protected user ids.
     */
    const PROTECTED_USER_IDS = [ 1 ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'name', 'firebase_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes casting.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * Dates converted to carbon instance.
     *
     * @var array
     */
    protected $dates = [ 'created_at', 'updated_at', 'email_verified_at', 'terms_accepted_at' ];

    /**
     * Possible relations.
     *
     * @return array
     */
    public function extraFields()
    {
        return [ 'roles', 'permissions' ];
    }


    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Return email field value for verification actions.
     *
     * @return string
     */
    public function getEmailForActionsVerification()
    {
        return $this->email;
    }

    /**
     * Send reset notification notification.
     *
     * @param array  $token
     * @param string $requestSide
     */
    public function sendPasswordResetNotification($token, $requestSide = self::FRONTEND_REQUEST_SIDE)
    {
        $this->notify(new ResetPassword($token, $requestSide));
    }

    /**
     * Send the email verification notification.
     *
     * @param array  $tokens
     * @param string $requestSide
     *
     * @return void
     */
    public function sendEmailVerificationNotification(array $tokens = [], $requestSide = self::FRONTEND_REQUEST_SIDE)
    {
        $this->notify(new VerifyEmail($tokens, $requestSide));
    }

    /**
     * Check if current user is protected.
     *
     * @return bool
     */
    public function isProtected()
    {
        return in_array($this->getKey(), self::PROTECTED_USER_IDS);
    }

    /**
     * Determine that current model can be changed(mby updated or deleted or etc)
     * by given user.
     *
     * @param \AttractCores\LaravelCoreAuth\Models\User $currentUser
     *
     * @return bool
     */
    public function canBeChangedByGivenUser(self $currentUser)
    {
        $checkUserOnActionRoles = $this->can(CorePermissionContract::CAN_ADMIN_ACCESS) ||
                                  $this->can(CorePermissionContract::CAN_OPERATOR_ACCESS);

        return (
                   $currentUser->isValidAdmin() && $checkUserOnActionRoles
               ) || ! $checkUserOnActionRoles;
    }

    /**
     * Generate soft deleted email pattern.
     *
     * @return string
     */
    public function softDeleteEmail()
    {
        return sprintf('%s-deleted-%s', $this->email, time());
    }

    /**
     * Route notifications for the FCM channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return string
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->firebase_token;
    }

}
