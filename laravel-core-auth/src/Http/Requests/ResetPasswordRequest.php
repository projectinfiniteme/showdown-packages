<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreAuth\Rules\ValidatePasswordStrength;
use Illuminate\Validation\Rule;

/**
 * Class ResetPasswordRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class ResetPasswordRequest extends ForgotPasswordRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'pwd-reset' => [
            'methods'    => [ 'POST' ],
            'permission' => 'default',
        ],
    ];

    /**
     * Post action rules
     *
     * @return array
     */
    public function postAction()
    {
        return [
            'token'    => [ 'required' ],
            'email'    => [ 'required', 'email', Rule::exists('users', 'email') ],
            'password' => [ 'required', 'confirmed', 'min:8', new ValidatePasswordStrength() ],
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
            'token.required'     => __("Token field is required."),
            'password.required'  => __("Password is required."),
            'password.confirmed' => __("Password  and confirmation field values should be equals to each other."),
            'password.min'       => __("Password should be :min characters in length."),
        ]);
    }

}
