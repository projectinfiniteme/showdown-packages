<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Class EmailVerificationRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class EmailVerificationRequest extends ForgotPasswordRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'resend'   => [
            'methods'    => [ 'POST' ],
            'route'      => '*/verification/resend',
            'permission' => CorePermissionContract::CAN_SIGN_IN,
        ],
        'e-verify' => [
            'methods'    => [ 'POST' ],
            'permission' => CorePermissionContract::CAN_SIGN_IN,
        ],
    ];

    /**
     * Authorize request actions.
     *
     * @return bool
     */
    public function authorize()
    {
        $result = parent::authorize();

        return $result && ! $this->user()->hasVerifiedEmail();
    }

    /**
     * Resend verification email rules.
     *
     * @return array|array[]
     */
    public function postResendAction()
    {
        return Arr::only(parent::postAction(), [ 'side' ]);
    }

    /**
     * Post action rules
     *
     * @return array
     */
    public function postAction()
    {
        return [
            'token' => [ 'required', 'string' ],
        ];
    }

    /**
     * Messages array.
     *
     * @return array
     */
    public function messagesArray()
    {
        return array_merge(parent::messagesArray(), [
            'token.required' => __("Token field is required."),
        ]);
    }

}
