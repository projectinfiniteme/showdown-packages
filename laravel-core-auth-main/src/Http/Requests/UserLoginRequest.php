<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreAuth\Rules\ValidateEmailIsNotBanned;
use AttractCores\LaravelCoreClasses\CoreFormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserLoginRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class UserLoginRequest extends CoreFormRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'sign-in' => [
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
            'email'          => [
                'bail', 'required', 'string', 'email', Rule::exists('users',
                    'email'), new ValidateEmailIsNotBanned($this->scopes ?? []),
            ],
            'password'       => [ 'required', 'string' ],
            'remember'       => [ 'sometimes', 'boolean' ],
            'firebase_token' => [ 'sometimes', 'string' ],
            'scopes'         => [ 'required', 'array', 'in:api,backend' ],
        ];
    }

    /**
     * Messages array.
     *
     * @return array
     */
    public function messagesArray()
    {
        return [
            'email.required'    => __("Email is required."),
            'email.email'       => __("Please, enter correct email."),
            'email.exists'      => __("Oops, this email is not registered with :App_name.",
                [ 'app_name' => config('app.name') ]),
            'password.required' => __("Password is required."),
        ];
    }

}
