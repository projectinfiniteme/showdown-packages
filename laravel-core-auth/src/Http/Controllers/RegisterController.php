<?php

namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use AttractCores\LaravelCoreAuth\Contracts\RegistrationContract;
use AttractCores\LaravelCoreAuth\Events\Registered;
use AttractCores\LaravelCoreAuth\Extensions\AuthenticatesUsers;
use AttractCores\LaravelCoreAuth\Http\Requests\UserRegisterRequest;
use AttractCores\LaravelCoreAuth\Http\Resources\UserResponseResource;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreClasses\CoreController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Class RegisterController
 *
 * @package AttractCores\LaravelCoreAuth\Http\Controllers
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class RegisterController extends CoreController
{

    use AuthenticatesUsers;

    /**
     * RegisterController constructor.
     *
     * @throws \Symfony\Component\CssSelector\Exception\InternalErrorException
     */
    public function __construct()
    {
        $this->detectPasswordClient();
        $this->middleware('guest');
    }


    /**
     * Handle a registration request for the application.
     *
     * @param UserRegisterRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function register(UserRegisterRequest $request)
    {
        // Create the user.
        $user = $this->create($request->validated());

        // Check that we should dispatch Registered event by our config.
        if ( config('kit-auth.should_dispatch_registered_event') ) {
            event(new Registered($user, CoreUserContract::FRONTEND_REQUEST_SIDE));
        }

        // Login user with guard if possible.
        if ( method_exists($guard = $this->guard(), 'login') ) {
            $guard->login($user);
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return Authenticatable $user
     */
    protected function create(array $data)
    {
        if ( $this->repository instanceof RegistrationContract ) {
            return $this->repository->register($data);
        }

        $user = app(CoreUserContract::class)->fill($data)->forceFill([
            'password'          => $data[ 'password' ],
            'terms_accepted_at' => now(),
        ]);

        $user->save();

        $user->actAs(CoreRoleContract::CAN_SIGN_IN, CoreRoleContract::CAN_USER);

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param Request         $request
     * @param Authenticatable $user
     *
     * @return mixed
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    protected function registered(Request $request, Authenticatable $user)
    {
        if ( $request->expectsJson() ) {
            if ( $request->scopes ) {
                // Registration by token works only with base request. Do not push FormRequest into login request.
                return $this->login(request(), 'Registration Form');
            }

            return $this->serverResponse()->resource(app(UserResponseResource::class, [ 'resource' => $user ]));
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if ( method_exists($this, 'redirectTo') ) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Return login url.
     *
     * @return string
     */
    protected function getOAuthLoginUrl()
    {
        return route('api.auth.login', [], false);
    }

}