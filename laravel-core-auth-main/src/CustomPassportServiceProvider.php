<?php

namespace AttractCores\LaravelCoreAuth;

use DateInterval;
use Illuminate\Http\Request;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;

class CustomPassportServiceProvider extends PassportServiceProvider
{

    /**
     * Register the authorization server.
     *
     * @return void
     */
    protected function registerAuthorizationServer()
    {
        Passport::tokensCan(config('kit-auth.passport-scopes'));

        $this->app->singleton(AuthorizationServer::class, function () {
            return tap($this->makeAuthorizationServer(), function (AuthorizationServer $server) {
                $server->setDefaultScope(Passport::$defaultScope);

                $this->enableGrants(request(), $server);
            });
        });
    }

    /**
     * Enable grants depends on app config.
     *
     * @param Request             $request
     * @param AuthorizationServer $server
     *
     * @throws \Exception
     */
    protected function enableGrants(Request $request, AuthorizationServer $server)
    {
        foreach (config('kit-auth.passport') as $grantSlug => $environment) {
            switch ($grantSlug) {
                case 'client-credentials-grant':
                    $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            new ClientCredentialsGrant, Passport::tokensExpireIn()
                        ) :
                        null;
                    break;
                case 'refresh-token-grant':
                    $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            $this->makeRefreshTokenGrant(), Passport::tokensExpireIn()
                        ) :
                        null;
                    break;
                case 'password-grant':
                    $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            $this->makePasswordGrant(), Passport::tokensExpireIn()
                        ) :
                        null;
                    break;
                case 'personal-access-grant':
                    $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            new PersonalAccessGrant, Passport::personalAccessTokensExpireIn()
                        ) :
                        null;
                    break;
                case 'implicit-grant':
                    Passport::$implicitGrantEnabled && $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            $this->makeImplicitGrant(), Passport::tokensExpireIn()
                        ) :
                        null;
                    break;
                case 'auth-code-grant':
                    $this->isEnableNeeded($request, $environment) ?
                        $server->enableGrantType(
                            $this->makeAuthCodeGrant(), Passport::tokensExpireIn()
                        ) :
                        null;
                    break;

            }
        }
    }

    /**
     * Check if grant type must be enabled.
     *
     * @param Request $request
     * @param array   $environment
     *
     * @return bool|string
     */
    protected function isEnableNeeded(Request $request, array $environment)
    {
        if ($environment['routes'][0] == '*' || $environment['app_envs'][0] == '*') {
            return true;
        }

        foreach ($environment['routes'] as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return $this->app->environment($environment['app_envs']);
    }
}
