<?php

namespace AttractCores\LaravelCoreMedia;

use AttractCores\LaravelCoreMedia\Commands\PruneOldMedia;
use AttractCores\LaravelCoreMedia\Libraries\MediaStorage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Http\Middleware\CheckScopes;

/**
 * Class CoreMediaServiceProvider
 *
 * @version 1.0.0
 * @date    2019-02-18
 * @author  Yure Nery <yurenery@gmail.com>
 */
class CoreMediaServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->aliasMiddleware('check-scopes', CheckScopes::class);

        if ( $this->app->runningInConsole() ) {
            $this->bootMigrations();
            $this->publishConfigurations();
            $this->publishTests();
            $this->publishPostman();
        }

        $this->configureRateLimiting();
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigurations();

        $this->commands([ PruneOldMedia::class ]);

        $this->app->singleton('kit-media.storage', function(){
            return new MediaStorage(config('kit-media'));
        });
    }

    /**
     * Merge Kit configuration files.
     */
    protected function mergeConfigurations()
    {
        $path = __DIR__ . '/../config/';
        $this->mergeConfigFrom($path . 'kit-media.php', 'kit-media');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishTests()
    {
        $this->publishes([
            __DIR__ . '/../tests/Feature'   => base_path('tests/Feature'),
            __DIR__ . '/../tests/resources'   => base_path('tests/resources'),
        ], 'attract-core-kit-media-tests');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishConfigurations()
    {

        $this->publishes([
            __DIR__ . '/../config/kit-media.php' => config_path('kit-media.php'),
        ], 'attract-core-kit-media-config');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishPostman()
    {
        $this->publishes([
            __DIR__ . '/../postman' => app_path('Postman'),
        ], 'attract-core-kit-media-postman-factories');
    }

    /**
     * Boot migrations of the Kit.
     */
    protected function bootMigrations()
    {
        $this->loadMigrationsFrom($path = $this->getMigrationsPath());
        $this->publishes([
            $path => database_path('migrations'),
        ], 'attract-core-kit-media-migrations');
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
     * Configure rate limiting.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('kit-media', function(Request $request){
            return Limit::perMinute(config('kit-media.requests_throttle', 15))->by(optional($request->user())->id ?: $request->bearerToken());
        });
    }

}
