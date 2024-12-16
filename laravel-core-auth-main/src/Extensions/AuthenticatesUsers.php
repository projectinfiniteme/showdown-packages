<?php

namespace AttractCores\LaravelCoreAuth\Extensions;

use AttractCores\LaravelCoreAuth\Events\UserTokenIssued;
use AttractCores\LaravelCoreAuth\Http\Resources\UserResponseResource;
use \AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ApiOauthAuthentication
 *
 * @version 1.0.0
 * @date    15.07.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait AuthenticatesUsers
{

    /**
     * Login user and create bearer token.
     *
     * @param Request $request
     * @param string  $loginType
     *
     * @return ServerResponse
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function login(Request $request, $loginType = 'Login Form')
    {
        if ( ! $request instanceof FormRequest && method_exists($this, 'validateLoginRequest') ) {
            $this->validateLoginRequest($request);
        }

        $request->merge([
            'grant_type'    => 'password',
            'username'      => $request->email,
            'password'      => $request->password,
            'client_id'     => config('kit-auth.password_grant.id'),
            'client_secret' => config('kit-auth.password_grant.secret'),
            'scope'         => $request->scopes,
        ]);

        $proxy = $request->create($this->getTokenUrl(), 'POST');

        return $this->runOAuthRequest($proxy, $loginType);
    }

    /**
     * Logout user from bearer token.
     *
     * @param Request $request
     *
     * @return ServerResponse
     * @throws \Exception
     */
    public function logout(Request $request)
    {

        /** @var \Laravel\Passport\Token $token */
        $token = ServerResponse::getCurrentToken($request);

        // Remove firebase token.
        if($token->user) {
            $token->user->firebase_token = NULL;
            $token->user->save();
        }

        if ( $token ) {
            $token->delete();
        }

        return $this->serverResponse();
    }

    /**
     * Refresh token request.
     *
     * @param Request $request
     *
     * @return ServerResponse
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function refreshToken(Request $request)
    {
        if ( method_exists($this, 'validateRefreshTokenRequest') ) {
            $this->validateRefreshTokenRequest($request);
        }

        $request->merge([
            'grant_type'    => 'refresh_token',
            'client_id'     => config('kit-auth.password_grant.id'),
            'client_secret' => config('kit-auth.password_grant.secret'),
        ]);

        $proxy = $request->create($this->getTokenUrl(), 'POST');

        return $this->runOAuthRequest($proxy, 'API Refresh');
    }


    /**
     * Return token url.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return route('api.oauth.passport.token');
    }

    /**
     * Run oauth request.
     *
     * @param Request $request
     * @param         $tokenName
     *
     * @return ServerResponse
     * @throws OAuthServerException
     */
    protected function runOAuthRequest(Request $request, $tokenName)
    {
        $mainRequest = request();
        $response = Route::dispatch($request);

        if ( $response->getStatusCode() == 200 ) {
            return $this->authenticateUser($mainRequest, $response, $tokenName);
        } elseif ( $mainRequest->is('*/login') ) {
            throw OAuthServerException::invalidCredentials();
        } elseif ( $mainRequest->is('*/refresh-token') ) {
            throw OAuthServerException::invalidRefreshToken();
        }
    }

    /**
     * Append user response.
     *
     * @param Request  $request
     * @param Response $response
     * @param          $tokenName
     *
     * @return ServerResponse
     */
    protected function authenticateUser(Request $request, Response $response, $tokenName)
    {
        // Get Response Data.
        $data = collect(json_decode($response->getContent(), true));

        // Sign token
        $token = $this->signToken($data, $tokenName);

        return $this->authenticated($request, $token, $data);
    }

    /**
     * Sign a token by form name.
     *
     * @param $tokenData
     * @param $tokenName
     *
     * @return \Laravel\Passport\Token
     */
    protected function signToken($tokenData, $tokenName)
    {
        // Detect token part from access token hash
        $jwt = app(Parser::class)->parse($tokenData[ 'access_token' ]);

        $token = app(TokenRepository::class)->find($jwt->claims()->get('jti'));

        // Save token with the form name.
        $token->name = sprintf("[%s] %s Token", config('app.name'), $tokenName);
        $token->save();

        return $token;
    }

    /**
     * Fetch the password oauth client.
     *
     * @throws \Symfony\Component\CssSelector\Exception\InternalErrorException
     */
    protected function detectPasswordClient()
    {
        if ( ! config('kit-auth.password_grant.id') || ! config('kit-auth.password_grant.secret') ) {
            throw new InternalErrorException("Password OAuth server can't be start. Please, provide APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_ID and APP_KIT_AUTH_PASSWORD_GRANT_CLIENT_SECRET env variables",
                500);
        }
    }

    /**
     * The user has been authenticated.
     *
     * @param Request $request
     * @param Token   $token
     * @param         $oauthResponse
     *
     * @return ServerResponse
     */
    protected function authenticated(Request $request, Token $token, $oauthResponse)
    {
        // Add firebaseToken to user.
        $token->user->setFireBaseToken($request->input('firebase_token', NULL));

        event(new UserTokenIssued($token));

        return $this->serverResponse()
            ->resource(app(UserResponseResource::class, [ 'resource' => $token->user ]))
            ->extend($oauthResponse);
    }

}
