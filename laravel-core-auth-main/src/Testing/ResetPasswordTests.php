<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use AttractCores\LaravelCoreAuth\Notifications\ResetPassword;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Trait ResetPasswordTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait ResetPasswordTests
{

    use TestRoutes;

    protected array $tokens;


    /**
     * Test backend reset password.
     *
     * @return void
     */
    public function testApiResetPassword()
    {

        // Setup
        $this->setItUp($this->getSendPasswordResetLinkRoute(), true);

        //SetupEnds

        $userData = $this->getData(true);
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getPasswordResetRoute(), $userData);

        $response->assertStatus(422);

        Event::fake();

        $userData = $this->getData();
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getPasswordResetRoute(), $userData);

        $response->assertStatus(200);
        Event::assertDispatched(PasswordReset::class, 1);
        $this->assertTrue(Hash::check($userData[ 'password' ],
            CoreUser::byEmail(config('kit-auth.start-user.email'))->firstOrFail()->password), 'Passwords missmatch.');
    }

    /**
     * Test backend reset password.
     *
     * @return void
     */
    public function testApiResetPasswordWithShortCode()
    {

        // Setup
        $this->setItUp($this->getSendPasswordResetLinkRoute(), true);

        //SetupEnds

        $userData = $this->getData(true);
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getPasswordResetRoute(), $userData);

        $response->assertStatus(422);

        Event::fake();

        $userData = $this->getData(false, false);
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getPasswordResetRoute(), $userData);

        $response->assertStatus(200);
        Event::assertDispatched(PasswordReset::class, 1);
        $this->assertTrue(Hash::check($userData[ 'password' ],
            CoreUser::byEmail(config('kit-auth.start-user.email'))->firstOrFail()->password), 'Passwords missmatch.');
    }

    /**
     * Generate data for reset token
     *
     * @param bool $fake
     * @param bool $web
     *
     * @return array
     */
    protected function getData($fake = false, $web = true)
    {
        return [
            'email'                 => $fake ? $this->faker->email : config('kit-auth.start-user.email'),
            'token'                 => $fake ? Str::random(60) :
                ( $web ? $this->tokens[ 'web' ] : $this->tokens[ 'mobile' ] ),
            'password'              => '11111111t&^T',
            'password_confirmation' => '11111111t&^T',
        ];
    }

    /**
     * Set up the test start.
     *
     * @param      $url
     * @param bool $withToken
     *
     * @throws \Exception
     */
    protected function setItUp($url, $withToken = false)
    {
        $notification = Notification::fake();

        if ( $withToken ) {
            $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ]);
        }

        $response = $this->json('POST', $url, [
            'email' => config('kit-auth.start-user.email'),
            'side'  => CoreUserContract::BACKEND_REQUEST_SIDE,
        ]);
        $response->assertStatus(200);
        $notification->assertSentTo(CoreUser::byEmail(config('kit-auth.start-user.email'))->firstOrFail(),
            ResetPassword::class, function (ResetPassword $notification) {
                $this->tokens = $notification->tokens;
                $this->assertEquals($notification->requestSide, CoreUserContract::BACKEND_REQUEST_SIDE);

                return true;
            });
    }

}