<?php

namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use AttractCores\LaravelCoreAuth\Http\Requests\ResetPasswordRequest;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class ResetPasswordController
 *
 * @package AttractCores\LaravelCoreAuth\Http\Controllers
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ResetPasswordController extends CoreController
{

    /**
     * Broker for verification actions.
     *
     * @var VerificationBrokerContract
     */
    protected VerificationBrokerContract $broker;

    /**
     * Describe default broker on type.
     *
     * @var string
     */
    protected string $brokerType = 'passwords';

    /**
     * ResetPasswordController constructor.
     *
     * @param VerificationBrokerContract $broker
     */
    public function __construct(VerificationBrokerContract $broker)
    {
        $this->middleware('guest');
        $this->broker = $broker;
    }

    /**
     * Reset the given user's password.
     *
     * @param ResetPasswordRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function reset(ResetPasswordRequest $request)
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker->verify(
            $this->credentials($request),
            $this->brokerType,
            function ($user, $password) {
                $this->resetPassword($user, $password);
            });

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == VerificationBrokerContract::PASSWORD_RESET_VERIFICATION_PASSES
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
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
     * Get the password reset credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|CoreUserContract $user
     * @param string                                                      $password
     *
     * @return void
     */
    protected function resetPassword($user, string $password)
    {
        $user->password = $password;

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \AttractCores\LaravelCoreClasses\Libraries\ServerResponse|\Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse(Request $request, string $response)
    {
        if ( $request->wantsJson() ) {
            return $this->serverResponse()->data([ 'message' => trans($response) ]);
        }

        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendResetFailedResponse(Request $request, string $response)
    {
        if ( $request->wantsJson() ) {
            throw ValidationException::withMessages([
                'email' => [ trans($response) ],
            ]);
        }

        return redirect()->back()
                         ->withInput($request->only('email'))
                         ->withErrors([ 'email' => trans($response) ]);
    }


}