<?php

namespace AttractCores\LaravelCoreAuth\Testing;

/**
 * Trait TestRoutes
 *
 * @package AttractCores\LaravelCoreAuth\Testing
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait TestRoutes
{
    /**
     * Route names for tests.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getOauthRoute($params = [])
    {
        return route('api.oauth.passport.token', $params);
    }

    /**
     * Route names for tests.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getSendPasswordResetLinkRoute($params = [])
    {
        return route('api.auth.password.forgot', $params);
    }

    /**
     * Route names for tests.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getPasswordResetRoute($params = [])
    {
        return route('api.auth.password.reset', $params);
    }

    /**
     * Return route for login.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getLoginRoute($params = [])
    {
        return route('api.auth.login', $params);
    }

    /**
     * Return route for refresh token.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getRefreshTokenRoute($params = [])
    {
        return route('api.oauth.passport.refresh-token', $params);
    }

    /**
     * Return route for login.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getLogoutRoute($params = [])
    {
        return route('api.auth.logout', $params);
    }

    /**
     * Return route for login.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getRegisterRoute($params = [])
    {
        return route('api.auth.register', $params);
    }

    /**
     * Return route for login.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getEmailVerificationRoute($params = [])
    {
        return route('api.auth.verification.email', $params);
    }

    /**
     * Return route for login.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getEmailResendVerificationRoute($params = [])
    {
        return route('api.auth.verification.resend', $params);
    }

}