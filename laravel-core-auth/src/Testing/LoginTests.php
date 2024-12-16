<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use AttractCores\LaravelCoreAuth\Events\UserTokenIssued;
use AttractCores\LaravelCoreAuth\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

/**
 * Trait LoginTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait LoginTests
{

    use TestRoutes;

    /**
     * Test api login.
     *
     * @return void
     */
    public function testApiLogin()
    {
        $eventFake = Event::fake([ UserTokenIssued::class ]);

        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getLoginRoute(), [
                             'email'          => config('kit-auth.start-user.email'),
                             'password'       => config('kit-auth.start-user.password'),
                             'firebase_token' => Str::random(20),
                             'scopes'         => $this->scopes,
                         ]);

        $response->assertSuccessful()->assertJsonMissingValidationErrors();
        $this->assertArrayHasKey('token_type', $response->decodeResponseJson()->json());
        $this->assertArrayHasKey('access_token', $response->decodeResponseJson()->json());
        $this->assertArrayHasKey('expires_in', $response->decodeResponseJson()->json());
        $this->assertArrayHasKey('refresh_token', $response->decodeResponseJson()->json());
        $eventFake->assertDispatched(UserTokenIssued::class);
    }

    /**
     * Test api login fails without client auth.
     *
     * @return void
     */
    public function testApiLoginFailedWithoutClientAuth()
    {
        $response = $this->json('POST', $this->getLoginRoute(), [
            'email'          => config('kit-auth.start-user.email'),
            'password'       => config('kit-auth.start-user.password'),
            'firebase_token' => Str::random(20),
            'scopes'         => $this->scopes,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test api refresh token.
     *
     * @return void
     */
    public function testApiRefreshToken()
    {
        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getLoginRoute(), [
                             'email'          => config('kit-auth.start-user.email'),
                             'password'       => config('kit-auth.start-user.password'),
                             'firebase_token' => Str::random(20),
                             'scopes'         => $this->scopes,
                         ]);

        $response->assertSuccessful()->assertJsonMissingValidationErrors();

        $response = $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                         ->json('POST', $this->getRefreshTokenRoute(), [
                             'refresh_token'  => $response->decodeResponseJson()->json('refresh_token'),
                             'firebase_token' => Str::random(20),
                         ]);

        $response->assertSuccessful()->assertJsonMissingValidationErrors();
        $this->assertArrayHasKey('token_type', $response->decodeResponseJson());
        $this->assertArrayHasKey('access_token', $response->decodeResponseJson());
        $this->assertArrayHasKey('expires_in', $response->decodeResponseJson());
        $this->assertArrayHasKey('refresh_token', $response->decodeResponseJson());
    }

}