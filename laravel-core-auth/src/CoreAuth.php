<?php

namespace AttractCores\LaravelCoreAuth;

use AttractCores\PostmanDocumentation\Facade\Markdown;
use Illuminate\Support\Facades\Route;

/**
 * Class CoreAuth
 *
 * @package AttractCores\LaravelCoreAuth
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreAuth
{

    /**
     * Add Oauth Routes.
     */
    public static function addOauthRoutes()
    {
        Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')
             ->name('oauth.passport.token')
             ->middleware('throttle:oauth2.0')
             ->aliasedName('Request authentication token')
             ->description(
                 Markdown::heading('Available scopes list')
                         ->numericList(config('kit-auth.passport-scopes'))
                         ->heading('Available `grant_type` options')
                         ->unorderedList([
                             'client_credentials' => 'Request token for device authentication',
                             'password'           => 'Request token for user authentication. *NOTE*: This type of authentication available only on `local` and `dev` envs.',
                         ])
             );
        Route::post('oauth/refresh-token',
            '\AttractCores\LaravelCoreAuth\Http\Controllers\ApiLoginController@refreshToken')
             ->name('oauth.passport.refresh-token')
             ->middleware('throttle:oauth2.0')
             ->aliasedName('Refresh authentication token');
    }

    /**
     * Add basic auth routes
     */
    public static function addAuthBasicRoutes()
    {
        Route::get('logout', '\AttractCores\LaravelCoreAuth\Http\Controllers\ApiLoginController@logout')
             ->name('logout')
             ->middleware('throttle:auth')
             ->aliasedName('User logout');
        Route::post('login', '\AttractCores\LaravelCoreAuth\Http\Controllers\ApiLoginController@login')
             ->name('login')
            // this throttle type used, cuz we will dispatch oauth routes fake calls to obtain the tokens
             ->middleware('throttle:oauth2.0')
             ->aliasedName('User login');
        Route::post('register', '\AttractCores\LaravelCoreAuth\Http\Controllers\RegisterController@register')
             ->name('register')
            // this throttle type used, cuz we will dispatch oauth routes fake calls to obtain the tokens
             ->middleware('throttle:oauth2.0')
             ->aliasedName('User registration');
    }

    /**
     * Add password forgot and password reset routes
     */
    public static function addPWDRoutes()
    {
        Route::prefix('password')
             ->as('password.')
             ->group(function () {
                 Route::post('forgot',
                     '\AttractCores\LaravelCoreAuth\Http\Controllers\ForgotPasswordController@sendResetLinkEmail')
                      ->name('forgot')
                      ->middleware('throttle:pwd-reset')
                      ->aliasedName('Request password reset email')
                      ->description(
                          Markdown::heading('Available `side` parameter list')
                                  ->numericList([
                                      'frontend' => 'All links and redirections will work with `frontend` part of the app.',
                                      'backend'  => 'All links and redirections will work with `backend` part of the app.',
                                  ])
                      );
                 Route::post('reset', '\AttractCores\LaravelCoreAuth\Http\Controllers\ResetPasswordController@reset')
                      ->name('reset')
                      ->middleware('throttle:pwd-reset')
                      ->aliasedName('Reset user password');
             });
    }

    /**
     * Add verification routes
     */
    public static function addVerificationRoutes()
    {
        Route::prefix('verification')
             ->as('verification.')
             ->group(function () {
                 // NOTE: order is important
                 Route::post('email',
                     '\AttractCores\LaravelCoreAuth\Http\Controllers\VerificationController@verify')
                      ->name('email')
                      ->middleware('throttle:verify')
                      ->aliasedName('Verify user email');
                 Route::post('resend',
                     '\AttractCores\LaravelCoreAuth\Http\Controllers\VerificationController@resend')
                      ->name('resend')
                      ->middleware('throttle:verify-resend')
                      ->aliasedName('Resend user verification email')
                      ->description(
                          Markdown::heading('Available `side` parameter list')
                                  ->numericList([
                                      'frontend' => 'All links and redirections will work with `frontend` part of the app.',
                                      'backend'  => 'All links and redirections will work with `backend` part of the app.',
                                  ])
                      );
             });
    }

    /**
     * Enable module routes.
     *
     * @param string $baseGroupPrefix
     * @param string $authGroupPrefix
     * @param bool   $withVerification
     */
    public static function enableRoutes($baseGroupPrefix = 'api', $authGroupPrefix = 'auth', $withVerification = true)
    {
        Route::prefix($baseGroupPrefix)
             ->as($baseGroupPrefix . '.')
             ->group(function () use ($withVerification, $authGroupPrefix) {
                 static::addOauthRoutes();

                 Route::middleware([ 'auth-api-client' ])
                      ->prefix($authGroupPrefix)
                      ->as($authGroupPrefix . '.')
                      ->group(function () use ($withVerification) {
                          static::addAuthBasicRoutes();
                          static::addPWDRoutes();

                          if ( $withVerification ) {
                              static::addVerificationRoutes();
                          }
                      });
             });
    }

}