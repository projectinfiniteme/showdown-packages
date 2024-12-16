<?php

namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use AttractCores\LaravelCoreAuth\Http\Requests\ForgotPasswordRequest;
use AttractCores\LaravelCoreAuth\Notifications\ResetPassword;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class ForgotPasswordController
 *
 * @package AttractCores\LaravelCoreAuth\Http\Controllers
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ForgotPasswordController extends CoreController
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
     * ForgotPasswordController constructor.
     *
     * @param VerificationBrokerContract $broker
     */
    public function __construct(VerificationBrokerContract $broker)
    {
        $this->middleware('guest');
        $this->broker = $broker;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param ForgotPasswordRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker->sendVerificationLink(
            $this->credentials($request),
            $this->brokerType,
            function ($user, $tokens)  use($request){
                if ( class_implements($user, CanResetPassword::class) ) {
                    $user->sendPasswordResetNotification($tokens, $request->input('side', CoreUserContract::FRONTEND_REQUEST_SIDE));
                } else {
                    throw new \UnexpectedValueException("User class must implements CanResetPassword interface to be able reset passwords.");
                }
            }
        );

        return $response == VerificationBrokerContract::PASSWORD_VERIFICATION_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \AttractCores\LaravelCoreClasses\Libraries\ServerResponse|\Illuminate\Http\RedirectResponse
     */
    protected function sendResetLinkResponse(Request $request, string $response)
    {
        return $request->wantsJson()
            ? $this->serverResponse()->data([ 'message' => trans($response) ])
            : back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, string $response)
    {
        if ( $request->wantsJson() ) {
            throw ValidationException::withMessages([
                'email' => [ trans($response) ],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([ 'email' => trans($response) ]);
    }

}