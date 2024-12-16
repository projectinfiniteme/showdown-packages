<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use AttractCores\LaravelCoreAuth\Notifications\ResetPassword;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Support\Facades\Notification;

/**
 * Trait ForgotPasswordTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait ForgotPasswordTests
{

    use TestRoutes;

    /**
     * Test api send email.
     *
     * @return void
     * @throws \Exception
     */
    public function testApiSendResetLink()
    {
        $response = $this->json('POST', $this->getSendPasswordResetLinkRoute(), [
            'email' => 'asd',
            'side'  => CoreUserContract::BACKEND_REQUEST_SIDE,
        ]);

        $response->assertStatus(401);

        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getSendPasswordResetLinkRoute(), [
                             'email' => 'asd',
                             'side'  => CoreUserContract::BACKEND_REQUEST_SIDE,
                         ]);

        $response->assertStatus(422);

        $notification = Notification::fake();

        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getSendPasswordResetLinkRoute(), [
                             'email' => config('kit-auth.start-user.email'),
                             'side'  => CoreUserContract::BACKEND_REQUEST_SIDE,
                         ]);

        $response->assertStatus(200);
        $notification->assertSentTo(CoreUser::byEmail(config('kit-auth.start-user.email'))->first(),
            ResetPassword::class);
    }

}