<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use Illuminate\Support\Str;

/**
 * Trait LogoutTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait LogoutTests
{

    use TestRoutes;

    /**
     * Test api login.
     *
     * @return void
     * @throws \Throwable
     */
    public function testApiLogout()
    {
        $response = $this->loginAdmin();

        $response->assertSuccessful()->assertJsonMissingValidationErrors();

        $content = $response->decodeResponseJson()->json();

        $this->assertNotEmpty($this->resolveUser()->findOrFail($content[ 'data' ][ 'id' ])->firebase_token);

        $response = $this->withHeaders([ 'Authorization' => $this->compileBearer($content[ 'access_token' ]) ])
                         ->getJson($this->getLogoutRoute());

        $response->assertSuccessful()->assertJsonMissingValidationErrors();

        $this->assertEmpty($this->resolveUser()->findOrFail($content[ 'data' ][ 'id' ])->firebase_token);
    }

    /**
     * Return response after login.
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function loginAdmin()
    {
        return $this->withHeaders([ 'Authorization' => $this->getBearerClientToken() ])
                    ->json('POST', $this->getLoginRoute(), [
                        'email'          => config('kit-auth.start-user.email'),
                        'password'       => config('kit-auth.start-user.password'),
                        'firebase_token' => Str::random(20),
                        'scopes'         => $this->scopes,
                    ]);
    }

}