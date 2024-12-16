<?php

namespace AttractCores\LaravelCoreAuth\Http\Requests;

use AttractCores\LaravelCoreClasses\CoreFormRequest;

/**
 * Class RefreshTokenRequest
 *
 * @package AttractCores\LaravelCoreAuth\Http\Requests
 */
class RefreshTokenRequest extends CoreFormRequest
{

    /**
     * Possible actions
     *
     * @var array
     */
    protected $actions = [
        'refresh' => [
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
            'refresh_token'  => [ 'required', 'string' ],
            'firebase_token' => [ 'sometimes', 'string' ],
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
            'refresh_token.required' => __("Refresh token is required."),
        ];
    }

}
