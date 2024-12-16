<?php

namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use AttractCores\LaravelCoreAuth\Events\UserTokenIssued;
use AttractCores\LaravelCoreAuth\Http\Requests\RefreshTokenRequest;
use AttractCores\LaravelCoreAuth\Http\Requests\UserLoginRequest;
use AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use AttractCores\LaravelCoreAuth\Http\Resources\UserResponseResource;
use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreAuth\Extensions\AuthenticatesUsers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Laravel\Passport\Token;

/**
 * Class ApiLoginController
 *
 * @version 1.0.0
 * @date    2019-02-22
 * @author  Yure Nery <yurenery@gmail.com>
 */
class ApiLoginController extends CoreController
{

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @throws \Symfony\Component\CssSelector\Exception\InternalErrorException
     */
    public function __construct()
    {
        $this->detectPasswordClient();
        $this->middleware('guest')->except([ 'logout' ]);
    }

    /**
     * Validate login request.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function validateLoginRequest(Request $request)
    {
        UserLoginRequest::createFrom($request)
                        ->setContainer(app())
                        ->setRedirector(redirect())
                        ->validateResolved();
    }

    /**
     * Validate refresh token request.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function validateRefreshTokenRequest(Request $request)
    {
        RefreshTokenRequest::createFrom($request)
                           ->setContainer(app())
                           ->setRedirector(redirect())
                           ->validateResolved();
    }

}
