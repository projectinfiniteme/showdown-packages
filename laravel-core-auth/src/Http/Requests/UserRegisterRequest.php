<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreAuth\Rules\ValidatePasswordStrength;
use AttractCores\LaravelCoreClasses\CoreFormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserRegisterRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class UserRegisterRequest extends CoreFormRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'register' => [
            'methods'    => [ 'POST' ],
            'permission' => 'default',
        ],
    ];

    /**
     * Messages array.
     *
     * @return array
     */
    public function messagesArray()
    {
        return [
            'email.required'     => __("Email is required."),
            'email.email'        => __("Please, enter correct email."),
            'email.unique'       => __("Email is already registered in the :App_name.",
                [ 'app_name' => config('app.name') ]),
            'password.required'  => __("Password is required."),
            'password.min'       => __("Password should be :min characters in length."),
            'password.confirmed' => __("Password  and confirmation field values should be equals to each other."),
        ];
    }

    /**
     * Post action rules
     *
     * @return array
     */
    public function postAction()
    {
        return [
            'email'          => [ 'bail', 'required', 'string', 'email', Rule::unique('users', 'email') ],
            'password'       => [ 'required', 'string', 'min:8', 'confirmed', new ValidatePasswordStrength() ],
            'firebase_token' => [ 'nullable', 'sometimes', 'string' ], // should be required for api, but on server it's sometimes.
            'name'           => [ 'nullable', 'sometimes', 'string' ],
            'scopes'         => [ 'sometimes', 'array', 'in:api' ],
        ];
    }

}
