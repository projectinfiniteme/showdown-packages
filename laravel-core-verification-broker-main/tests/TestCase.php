<?php

namespace Tests;

use AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions;
use AttractCores\LaravelCoreVerificationBroker\VerificationBrokerServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Facade;
use Orchestra\Testbench\TestCase as CoreTestCase;

abstract class TestCase extends CoreTestCase
{
    use RefreshDatabase;

    public function setUp() : void
    {
        Facade::clearResolvedInstances();
        parent::setUp();

        config()->set('auth.providers.users.model', User::class);
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        $app->afterResolving('migrator', function ($migrator) {
            $migrator->path(__DIR__ . '/Migrations/2014_10_12_000000_create_users_table.php');
        });

        return [
            VerificationBrokerServiceProvider::class,
        ];
    }

}

class User extends \Illuminate\Foundation\Auth\User implements CanVerifyActions
{

    public function getEmailForActionsVerification()
    {
        return $this->email;
    }

}