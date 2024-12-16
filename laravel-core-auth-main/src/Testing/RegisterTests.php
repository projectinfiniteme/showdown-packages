<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use AttractCores\LaravelCoreAuth\Events\Registered;
use AttractCores\LaravelCoreAuth\Listeners\ProcessEmailVerification;
use AttractCores\LaravelCoreAuth\Notifications\VerifyEmail;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Trait RegisterTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait RegisterTests
{

    use TestRoutes;

    /**
     * Test api registration validation.
     *
     * @return void
     * @throws \Throwable
     */
    public function testApiRegistrationValidation()
    {
        $notUnique = $this->getTestRegisterData(false, 5);
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getRegisterRoute(), $notUnique);

        $response->assertStatus(422);
        $errors = collect($response->decodeResponseJson()->json('errors'));
        $this->assertEquals(2, count($errors));
        $this->assertTrue($errors->has('password'));
        $this->assertTrue($errors->has('email'));
    }

    /**
     * Test api registration validation.
     *
     * @return void
     * @throws \Throwable
     */
    public function testApiRegistrationSimple()
    {
        $eventsFaker = Event::fake();
        $emailVerificationSend = Notification::fake();
        $uniqueData = $this->getTestRegisterData();
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getRegisterRoute(), $uniqueData);

        $response->assertStatus(200)->assertJsonMissingValidationErrors();

        $responseArray = $response->decodeResponseJson()->json();
        $this->assertArrayNotHasKey('token_type', $responseArray);
        $this->assertArrayNotHasKey('expires_in', $responseArray);
        $this->assertArrayNotHasKey('access_token', $responseArray);
        $this->assertArrayNotHasKey('refresh_token', $responseArray);

        $user = CoreUser::byEmail($uniqueData[ 'email' ])->firstOrFail();
        $this->withoutExceptionHandling();

        if ( config('kit-auth.should_dispatch_registered_event') ) {
            $eventsFaker->assertDispatched(Registered::class,
                function (Registered $event) use ($emailVerificationSend) {
                    app(ProcessEmailVerification::class)->handle($event);

                    $emailVerificationSend->assertSentTo($event->user, VerifyEmail::class,
                        function (VerifyEmail $notice) {
                            $this->assertEquals($notice->requestSide, CoreUserContract::FRONTEND_REQUEST_SIDE);
                            return $notice->tokens[ 'web' ] && $notice->tokens[ 'mobile' ];
                        });

                    return true;
                });
        } else {
            $eventsFaker->assertNotDispatched(Registered::class);
        }
    }

    /**
     * Test api registration validation.
     *
     * @return void
     * @throws \Throwable
     */
    public function testApiRegistrationWithTokenResponse()
    {

        $eventsFaker = Event::fake();
        $emailVerificationSend = Notification::fake();
        $uniqueData = $this->getTestRegisterData();
        $uniqueData[ 'scopes' ] = [ 'api' ];
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getRegisterRoute(), $uniqueData);

        $response->assertStatus(200)->assertJsonMissingValidationErrors();

        $responseArray = $response->decodeResponseJson()->json();
        $this->assertArrayHasKey('token_type', $responseArray);
        $this->assertArrayHasKey('expires_in', $responseArray);
        $this->assertArrayHasKey('access_token', $responseArray);
        $this->assertArrayHasKey('refresh_token', $responseArray);

        $user = CoreUser::byEmail($uniqueData[ 'email' ])->firstOrFail();
        $this->withoutExceptionHandling();

        if ( config('kit-auth.should_dispatch_registered_event') ) {
            $eventsFaker->assertDispatched(Registered::class,
                function (Registered $event) use ($emailVerificationSend) {
                    app(ProcessEmailVerification::class)->handle($event);

                    $emailVerificationSend->assertSentTo($event->user, VerifyEmail::class,
                        function (VerifyEmail $notice) {
                            return $notice->tokens[ 'web' ] && $notice->tokens[ 'mobile' ];
                        });

                    return true;
                });
        } else {
            $eventsFaker->assertNotDispatched(Registered::class);
        }
    }

    /**
     * Return test data.
     *
     * @param bool $unique
     * @param int  $passLength
     *
     * @return array
     */
    protected function getTestRegisterData($unique = true, $passLength = 8)
    {
        $password = Str::random($passLength);

        if ( $unique ) {
            $password .= '1t&^T';
        }

        return [
            'email'                 => $unique ? $this->faker->unique()->safeEmail :
                config('kit-auth.start-user.email'),
            'name'                  => $this->faker->name,
            'password'              => $password,
            'password_confirmation' => $password,
            'firebase_token'        => Str::random(60),
            'terms_accepted'        => true,
        ];
    }

}