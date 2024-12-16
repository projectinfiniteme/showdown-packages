<?php

namespace AttractCores\LaravelCoreVerificationBroker;

use AttractCores\LaravelCoreVerificationBroker\Contracts\TokenRepositoryInterface;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationTokenInterface;
use AttractCores\LaravelCoreVerificationBroker\Models\VerificationToken;
use AttractCores\LaravelCoreVerificationBroker\Repositories\TokenRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Class VerificationBrokerServiceProvider
 *
 * @package AttractCores\LaravelCoreVerificationBroker
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerificationBrokerServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Set up core.
        if ( $this->app->runningInConsole() ) {
            $this->bootMigrations();
            $this->publishConfigurations();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigurations();

        $this->app->bind(TokenRepositoryInterface::class, function(){
            return new TokenRepository(
                app(VerificationTokenInterface::class),
                $this->app['hash'],
                $this->app['config']['app.key'],
                config('verification-broker')
            );
        });

        $this->app->bind(VerificationBrokerContract::class, function($app){
            return new VerificationBroker(
                app(TokenRepositoryInterface::class),
                $app['auth']->createUserProvider('users')
            );
        });

        $this->app->bind(VerificationTokenInterface::class, function(){
            return new VerificationToken();
        });
    }

    /**
     * Boot migrations of the Kit.
     */
    protected function bootMigrations()
    {
        $this->loadMigrationsFrom($path = $this->getMigrationsPath());
        $this->publishes([
            $path => database_path('migrations'),
        ], 'attract-core-verification-broker-migrations');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishConfigurations()
    {

        $this->publishes([
            __DIR__ . '/../config/verification-broker.php' => config_path('verification-broker.php'),
        ], 'attract-core-verification-broker-config');
    }

    /**
     * Merge Kit configuration files.
     */
    protected function mergeConfigurations()
    {
        $path = __DIR__ . '/../config/';
        $this->mergeConfigFrom($path . 'verification-broker.php', 'verification-broker');
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
}