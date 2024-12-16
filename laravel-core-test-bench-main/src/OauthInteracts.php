<?php

namespace AttractCores\LaravelCoreTestBench;

use Illuminate\Testing\TestResponse;
use Laravel\Passport\Client;

/**
 * Trait OauthInteracts
 *
 * @version 1.0.0
 * @date    2019-03-12
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait OauthInteracts
{
    use UserClassResolver;

    /**
     * Array of used tokens.
     *
     * @var array
     */
    protected array $authTokens = [];

    protected array $scopes = [ 'backend', 'api' ];

    /**
     * Oauth tokens api route name.
     *
     * @var string
     */
    protected string $oauthRouteName = 'api.oauth.passport.token';

    /**
     * Return bearer token string.
     *
     * @return string
     */
    protected function getBearerClientToken()
    {
        return 'Bearer ' . $this->getClientToken();
    }

    /**
     * Return valid client token
     *
     * @return string
     * @throws \Throwable
     */
    protected function getClientToken()
    {
        /** @var Client $client */
        $client = $this->getApiOauthClient();

        /** @var TestResponse $response */
        $response = $this->json('POST', route($this->oauthRouteName), [
            'grant_type'    => 'client_credentials',
            'client_id'     => $client->getKey(),
            'client_secret' => $client->secret,
            'scope'         => $this->scopes,
        ]);

        return $this->getAccessTokenFromResponse($response);
    }

    /**
     * Return password access client.
     *
     * @return mixed
     */
    protected function getApiOauthClient()
    {
        return Client::where('password_client', false)->where('personal_access_client', false)->firstOrFail();
    }

    /**
     * Return admin token.
     *
     * @param        $rolesArray
     * @param string $password
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function getRandomUserToken($rolesArray, $password = '11111111')
    {
        // Fresh request.
        $this->freshRequest();

        /** @var User $user */
        $user = $this->resolveUserFactory()->create([
            'password' => $password,
        ]);

        $user->actAs($rolesArray);

        // Return user token.
        $response = $this->getUserToken($user->email, $password);

        // Fresh request.
        $this->freshRequest();

        return $this->authTokens[ $user->email ] = $this->getAccessTokenFromResponse($response);
    }

    /**
     * Refresh request.
     */
    protected function freshRequest()
    {
        $this->flushHeaders();
        $this->flushSession();
    }

    /**
     * Return token for given user.
     *
     * @param      $email
     * @param      $password
     *
     * @return TestResponse
     */
    protected function getUserToken($email, $password)
    {
        /** @var Client $client */
        $client = $this->getPasswordOauthClient();

        /** @var TestResponse $response */
        $response = $this->json('POST', route($this->oauthRouteName), [
            'grant_type'    => 'password',
            'client_id'     => $client->getKey(),
            'client_secret' => $client->secret,
            'username'      => $email,
            'password'      => $password,
            'scope'         => $this->scopes,
        ]);

        return $response;
    }

    /**
     * Return password access client.
     *
     * @return mixed
     */
    protected function getPasswordOauthClient()
    {
        return Client::where('password_client', true)->firstOrFail();
    }

    /**
     * Set auth token for authorization.
     *
     * @param null $token
     *
     * @throws \Throwable
     */
    protected function withAuthorizationToken($token = NULL)
    {
        // Check if token is TestResponse instance, decode it automatically.
        if ( $token instanceof TestResponse ) {
            $token = $this->getAccessTokenFromResponse($token);
        }

        $token = $this->getUserBearerToken($token);

        $this->withHeader('Authorization', $token);
    }

    /**
     * Return bearer token string.
     *
     * @param null $token
     *
     * @return string
     * @throws \Throwable
     */
    protected function getUserBearerToken($token = NULL)
    {
        return $this->compileBearer($token ?? $this->getAdminToken());
    }

    /**
     * Return compiled bearer token.
     *
     * @param $token
     *
     * @return string
     */
    protected function compileBearer($token)
    {
        return 'Bearer ' . $token;
    }

    /**
     * Return admin token.
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function getAdminToken()
    {
        // Fresh request.
        $this->freshRequest();

        /** @var Client $client */
        $client = $this->getPasswordOauthClient();

        $defaultAdminCredentials = $this->getDefaultAdminCredentials();

        /** @var TestResponse $response */
        $response = $this->json('POST', route($this->oauthRouteName), [
            'grant_type'    => 'password',
            'client_id'     => $client->getKey(),
            'client_secret' => $client->secret,
            'username'      => $email = $defaultAdminCredentials[ 'email' ],
            'password'      => $defaultAdminCredentials[ 'password' ],
            'scope'         => $this->scopes,
        ]);

        // Fresh request.
        $this->freshRequest();

        return $this->authTokens[ $email ] = $this->getAccessTokenFromResponse($response);
    }

    /**
     * Return user email by token after random generation.
     *
     * @param $token
     *
     * @return mixed
     */
    protected function getUserEmailByToken($token)
    {
        return collect($this->authTokens)->search($token);
    }

    /**
     * Return default admin credentials.
     *
     * @return array
     */
    protected function getDefaultAdminCredentials()
    {
        return config('kit-auth.start-user');
    }

    /**
     * Return access token from response
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @param string                           $key
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function getAccessTokenFromResponse(TestResponse $response, string $key = 'access_token')
    {
        $json = $response->decodeResponseJson();

        if ( $json->offsetExists($key) ) {
            return $json->json($key);
        } elseif ( $json->offsetExists('oauth') ) {
            return $json->json('oauth.' . $key);
        }

        return NULL;
    }

    /**
     * Dump given response data and DIE
     *
     * @param \Illuminate\Testing\TestResponse $response
     *
     * @throws \Throwable
     */
    protected function ddResponse(TestResponse $response)
    {
        dd($response->decodeResponseJson()->json());
    }

    /**
     * Dump given response data
     *
     * @param \Illuminate\Testing\TestResponse $response
     *
     * @throws \Throwable
     */
    protected function dumpResponse(TestResponse $response)
    {
        dump($response->decodeResponseJson()->json());
    }

}
