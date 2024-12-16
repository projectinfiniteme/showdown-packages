<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreClasses\CoreFormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ForgotPasswordRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class ForgotPasswordRequest extends CoreFormRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'pwd-forgot' => [
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
            'email' => [ 'bail', 'required', 'email', Rule::exists('users', 'email') ],
            'side'  => [ 'bail', 'required', Rule::in([ CoreUserContract::FRONTEND_REQUEST_SIDE, CoreUserContract::BACKEND_REQUEST_SIDE ]) ],
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
            'email.required' => __("Email is required."),
            'email.email'    => __("Please, enter correct email."),
            'email.exists'   => __("User with given email does not exist :App_name.",
                [ 'app_name' => config('app.name') ]),
            'side.required'  => __("Please, choose side for reset link generation."),
            'side.in'        => __("Side parameter can only be equals to one of these items: frontend, backend."),
        ];
    }

}
