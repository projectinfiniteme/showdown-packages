<?php

namespace AttractCores\LaravelCoreAuth;

use AttractCores\LaravelCoreAuth\Commands\PassportRevokeExpiredTokens;
use AttractCores\LaravelCoreAuth\Commands\PrunePassportRevokedAccessTokens;
use AttractCores\LaravelCoreAuth\Http\Controllers\AccessTokenController;
use AttractCores\LaravelCoreAuth\Http\Middleware\CheckClientCredentials;
use AttractCores\LaravelCoreAuth\Http\Middleware\SetUserExtensions;
use AttractCores\LaravelCoreAuth\Models\Permission;
use AttractCores\LaravelCoreAuth\Models\Role;
use AttractCores\LaravelCoreAuth\Models\User;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermission;
use AttractCores\LaravelCoreAuth\Resolvers\CorePermissionContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreRoleContract;
use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Laravel\Passport\Passport;

/**
 * Class CoreAuthServiceProvider
 *
 * @package CoreAuth
 * Date: 11.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreAuthServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Routing\Router        $router
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        // Set up passport.
        Passport::tokensExpireIn(now()->addSeconds(config('kit-auth.life_time.access_token')));
        Passport::refreshTokensExpireIn(now()->addSeconds(config('kit-auth.life_time.refresh_token')));
        Passport::loadKeysFrom(storage_path('oauth-keys/'));

        // Set up middlewares.
        $router->aliasMiddleware('auth-api-client', CheckClientCredentials::class);
        $router->aliasMiddleware('check-scopes', CheckScopes::class);
        $kernel->pushMiddleware(SetUserExtensions::class);

        // Set up core.
        if ( $this->app->runningInConsole() ) {
            $this->bootMigrations();

            $this->commands([
                PrunePassportRevokedAccessTokens::class,
                PassportRevokeExpiredTokens::class,
            ]);

            // Make publishes
            $this->publishConfigurations();
            $this->publishSeeders();
            $this->publishTests();
            $this->publishPostman();
        }

        // Boot Rate Limiting
        $this->configureRateLimiting();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\Laravel\Passport\Http\Controllers\AccessTokenController::class, AccessTokenController::class);

        $this->mergeConfigurations();

        $this->registerSingletons();

    }

    /**
     * Boot configuration publications.
     */
    protected function publishConfigurations()
    {

        $this->publishes([
            __DIR__ . '/../config/kit-auth.php' => config_path('kit-auth.php'),
        ], 'attract-core-kit-auth-config');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishSeeders()
    {
        $this->publishes([
            __DIR__ . '/../database/seeders/DefaultAdminSeeder.php'               => database_path('seeders/DefaultAdminSeeder.php'),
            __DIR__ .
            '/../database/seeders/DefaultRolesAndPermissionsSeeder.php'           => database_path('seeders/DefaultRolesAndPermissionsSeeder.php'),
        ], 'attract-core-kit-auth-seeders');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishTests()
    {
        $this->publishes([
            __DIR__ . '/../tests/Feature' => base_path('tests/Feature'),
        ], 'attract-core-kit-auth-tests');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishPostman()
    {
        $this->publishes([
            __DIR__ . '/../postman' => app_path('Postman'),
        ], 'attract-core-kit-auth-postman-factories');
    }

    /**
     * Merge Kit configuration files.
     */
    protected function mergeConfigurations()
    {
        $path = __DIR__ . '/../config/';
        $this->mergeConfigFrom($path . 'kit-auth.php', 'kit-auth');
    }

    /**
     * Boot migrations of the Kit.
     */
    protected function bootMigrations()
    {
        $this->loadMigrationsFrom($path = $this->getMigrationsPath());
        $this->publishes([
            $path => database_path('migrations'),
        ], 'attract-core-kit-auth-migrations');
    }


    /**
     * Register core singletons.
     */
    protected function registerSingletons()
    {
        $this->app->{$this->app->runningUnitTests() ? 'bind' : 'singleton'}('kit-auth.permissions', function ($app) {
            try {
                return CorePermission::with('roles')->get();
            } catch ( \Exception $e ) {
                report($e);

                return collect([]);
            }
        });

        $this->app->singleton(CoreUserContract::class, function ($app) {
            return new User;
        });

        $this->app->singleton(CorePermissionContract::class, function ($app) {
            return new Permission;
        });

        $this->app->singleton(CoreRoleContract::class, function ($app) {
            return new Role;
        });
    }

    /**
     * Migrations path.
     *
     * @return string
     */
    protected function getMigrationsPath()
    {
        return __DIR__ . '/../database/migrations';
    }

    /**
     * Configure Rate limiting for module.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('oauth2.0', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(30)->by($request->bearerToken());
        });

        RateLimiter::for('pwd-reset', function (Request $request) {
            return Limit::perMinute(10)->by($request->bearerToken());
        });

        RateLimiter::for('verify', function (Request $request) {
            return Limit::perMinute(5)->by(optional($request->user())->id ?: $request->bearerToken());
        });

        RateLimiter::for('verify-resend', function (Request $request) {
            return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->bearerToken());
        });
    }

}