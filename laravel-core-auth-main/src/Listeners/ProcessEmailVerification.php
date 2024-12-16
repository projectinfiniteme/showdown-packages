<?php

namespace AttractCores\LaravelCoreAuth\Listeners;

use AttractCores\LaravelCoreAuth\Events\Registered;
use AttractCores\LaravelCoreAuth\Http\Controllers\VerificationController;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class ProcessEmailVerification
 *
 * @package AttractCores\LaravelCoreAuth\Listeners
 * Date: 15.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ProcessEmailVerification implements ShouldQueue
{
    /**
     * @var VerificationBrokerContract
     */
    protected VerificationBrokerContract $broker;

    /**
     * ProcessEmailVerification constructor.
     *
     * @param VerificationBrokerContract $broker
     */
    public function __construct(VerificationBrokerContract $broker)
    {

        $this->broker = $broker;
    }

    /**
     * Handle the event.
     *
     * @param Registered $event
     *
     * @return void
     */
    public function handle(Registered $event)
    {
        $user = $event->user;
        if ( $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail() ) {
            $this->broker->sendVerificationLink([ 'email' => $user->email ],
                VerificationController::BROKER_TYPE,
                function (MustVerifyEmail $user, $tokens) use ($event) {
                    $user->sendEmailVerificationNotification($tokens, $event->requestSide);
                });
        }
    }

}