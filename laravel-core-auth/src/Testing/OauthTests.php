<?php

namespace AttractCores\LaravelCoreAuth\Testing;

use Illuminate\Support\Str;
use Laravel\Passport\Client;

/**
 * Trait OauthTests
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait OauthTests
{

    use TestRoutes;

    /**
     * Test that devices can sig-in
     */
    public function testOauthDeviceLogin()
    {
        /** @var Client $client */
        $client = $this->getApiOauthClient();

        $response = $this->json('POST', $this->getOauthRoute(), [
            'grant_type'    => 'client_credentials',
            'client_id'     => $client->getKey(),
            'client_secret' => $client->secret,
            'scopes'        => [ '*' ],
        ]);

        $response->assertSuccessful()
                 ->assertJsonCount(3)
                 ->assertJsonMissingValidationErrors();

        $this->assertArrayHasKey('token_type', $response->decodeResponseJson()->json());
        $this->assertArrayHasKey('access_token', $response->decodeResponseJson()->json());
        $this->assertArrayHasKey('expires_in', $response->decodeResponseJson()->json());
    }

    /**
     * Test that users cant sig-in though oauth server.
     */
    public function testOauthPasswordCantLogin()
    {
        // Disable passport grant type for testing
        $this->app[ 'env' ] = Str::random(5);

        /** @var Client $client */
        $client = $this->getPasswordOauthClient();

        $response = $this->json('POST', $this->getOauthRoute(), [
            'grant_type'    => 'password',
            'client_id'     => $client->getKey(),
            'client_secret' => $client->secret,
            'scopes'        => [ '*' ],
        ]);

        $response->assertStatus($this->getCantLoginStatus());
    }

    /**
     * Return status for handler catchers.
     *
     * @return int
     */
    protected function getCantLoginStatus()
    {
        return 500;
    }

}