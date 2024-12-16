<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use AttractCores\LaravelCoreAuth\Events\VerificationResend;
use AttractCores\LaravelCoreAuth\Listeners\ProcessEmailVerification;
use AttractCores\LaravelCoreAuth\Models\Role;
use AttractCores\LaravelCoreAuth\Notifications\VerifyEmail;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

/**
 * Trait VerificationTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait VerificationTests
{

    use TestRoutes;

    /**
     * Route names for tests.
     *
     * @var string
     */
    protected $apiVerificationRouteName = 'api.auth.verification.email';

    protected $apiVerificationResendRouteName = 'api.auth.verification.resend';

    /**
     * Test verification redirects and errors.
     *
     * @return void
     * @throws \Throwable
     */
    public function testVerificationErrors()
    {
        $token = $this->getRandomUserToken([ Role::CAN_SIGN_IN ]);
        $email = $this->getUserEmailByToken($token);

        /** @var \AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract $user */
        $user = CoreUser::byEmail($email)->first();
        $user->email_verified_at = now();
        $user->save();

        $response = $this->withHeaders([ 'Authorization' => $this->compileBearer($token) ])
                         ->json('POST', $this->getEmailVerificationRoute());
        $response->assertStatus(403);
    }

    /**
     * Test verification redirects and errors.
     *
     * @return void
     * @throws \Throwable
     */
    public function testVerification()
    {
        $token = $this->getRandomUserToken([ CoreRoleContract::CAN_SIGN_IN ]);
        $email = $this->getUserEmailByToken($token);

        /** @var \AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract $user */
        $user = CoreUser::byEmail($email)->first();
        $user->email_verified_at = NULL;
        $user->save();

        $generatedTokens = [];

        app(VerificationBrokerContract::class)
            ->sendVerificationLink(
                [ 'email' => $email ],
                'email_verification',
                function ($user, $tokens) use (&$generatedTokens) {
                    $generatedTokens = $tokens;
                });

        Event::fake();

        $response = $this->withHeaders([ 'Authorization' => $this->compileBearer($token) ])
                         ->json('POST', $this->getEmailVerificationRoute(), [
                             'token' => $this->faker->randomElement(array_values($generatedTokens)),
                         ]);
        $response->assertStatus(200);

        // Fresh
        $user = $user->fresh();

        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole(CoreRoleContract::CAN_SIGN_IN));
        Event::assertDispatched(Verified::class, 1);
    }

    /**
     * Test verification redirects and errors.
     *
     * @return void
     * @throws \Throwable
     */
    public function testVerificationResend()
    {
        $token = $this->getRandomUserToken([ CoreRoleContract::CAN_SIGN_IN ]);
        $email = $this->getUserEmailByToken($token);

        /** @var \AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract $user */
        $user = CoreUser::byEmail($email)->first();
        $user->email_verified_at = NULL;
        $user->save();

        $faker = Event::fake();
        $noticeFaker = Notification::fake();

        $response = $this->withHeaders([ 'Authorization' => $this->compileBearer($token) ])
                         ->json('POST', $this->getEmailResendVerificationRoute(), [
                             'side'  => CoreUserContract::FRONTEND_REQUEST_SIDE,
                         ]);
        $response->assertStatus(200);

        $faker->assertDispatched(VerificationResend::class, function (VerificationResend $event) use ($noticeFaker) {
            app(ProcessEmailVerification::class)->handle($event);

            $noticeFaker->assertSentTo($event->user, VerifyEmail::class, function (VerifyEmail $notice) {
                $this->assertEquals($notice->requestSide, CoreUserContract::FRONTEND_REQUEST_SIDE);

                return $notice->tokens[ 'web' ] && $notice->tokens[ 'mobile' ];
            });

            return true;
        });
    }

}