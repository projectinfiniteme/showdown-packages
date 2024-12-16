<?php

namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use AttractCores\LaravelCoreAuth\Events\VerificationResend;
use AttractCores\LaravelCoreAuth\Http\Requests\EmailVerificationRequest;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class VerificationController
 *
 * @package AttractCores\LaravelCoreAuth\Http\Controllers
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerificationController extends CoreController
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
    const BROKER_TYPE = 'email_verification';

    /**
     * VerificationController constructor.
     *
     * @param VerificationBrokerContract $broker
     */
    public function __construct(VerificationBrokerContract $broker)
    {
        $this->middleware('auth:api');
        $this->broker = $broker;
    }

    /**
     * Verify email by given credentials.
     *
     * @param \AttractCores\LaravelCoreAuth\Http\Requests\EmailVerificationRequest $request
     *
     * @return \AttractCores\LaravelCoreClasses\Libraries\ServerResponse|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verify(EmailVerificationRequest $request)
    {
        // Verify given credentials with broker.
        $response = $this->broker->verify(
            [ 'email' => $request->user()->email, 'token' => $request->input('token') ],
            static::BROKER_TYPE,
            function ($user) {
                if ( $user->markEmailAsVerified() ) {
                    event(new Verified($user));
                }
            });

        // If the token was successfully validated, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == VerificationBrokerContract::VERIFICATION_PASSES
            ? $this->sendVerifyResponse($request, $response)
            : $this->sendVerifyFailedResponse($request, $response);
    }

    /**
     * Resend verification email.
     *
     * @param \AttractCores\LaravelCoreAuth\Http\Requests\EmailVerificationRequest $request
     *
     * @return \AttractCores\LaravelCoreClasses\Libraries\ServerResponse
     */
    public function resend(EmailVerificationRequest $request)
    {
        event(new VerificationResend($request->user(),
            $request->input('side', CoreUserContract::FRONTEND_REQUEST_SIDE)));

        return $this->serverResponse()
                    ->data([ "message" => __('We have been send a new mail to you with verification instructions.') ]);
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
        return $request->only('email', 'token');
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \AttractCores\LaravelCoreClasses\Libraries\ServerResponse|\Illuminate\Http\RedirectResponse
     */
    protected function sendVerifyResponse(Request $request, string $response)
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
    protected function sendVerifyFailedResponse(Request $request, string $response)
    {
        if ( $request->wantsJson() ) {
            throw ValidationException::withMessages([
                'token' => [ trans($response) ],
            ]);
        }

        return redirect()->back()
                         ->withInput($request->only('email'))
                         ->withErrors([ 'email' => trans($response) ]);
    }

}