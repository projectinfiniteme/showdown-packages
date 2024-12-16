<?php

namespace Tests;

use AttractCores\LaravelCoreClasses\CoreControllerServiceProvider;
use AttractCores\LaravelCoreMedia\Contracts\HasMedia;
use AttractCores\LaravelCoreMedia\Contracts\MediaInteractable;
use AttractCores\LaravelCoreMedia\CoreMedia;
use AttractCores\LaravelCoreMedia\CoreMediaServiceProvider;
use AttractCores\LaravelCoreMedia\Extensions\ImplementMedia;
use AttractCores\LaravelCoreMedia\Extensions\MediaInteractions;
use AttractCores\LaravelCoreMedia\Http\Resources\MediaResource;
use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Models\Media;
use AttractCores\LaravelCoreMedia\Repositories\MediaRepository;
use AttractCores\LaravelCoreTestBench\OauthInteracts;
use AttractCores\PostmanDocumentation\PostmanServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as CoreUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\Factories\UserFactory;
use Orchestra\Testbench\TestCase as CoreTestCase;

abstract class TestCase extends CoreTestCase
{
    use RefreshDatabase, WithFaker, OauthInteracts;

    public function setUp() : void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Passport::loadKeysFrom(__DIR__ . '/keys/');
        Passport::tokensCan([ 'backend' => 'Backend access', 'api' => 'Api access' ]);
        Artisan::call('passport:keys', [ '--force' => true ]);

        // Clear framework data.
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('event:clear');

        // Create clients.
        Artisan::call('passport:client',
            [ '--client' => true, '--name' => 'Clients', '--redirect_uri' => config('app.url'), '--user_id' => NULL, '--provider' => 'users' ]);
        Artisan::call('passport:client',
            [ '--password' => true, '--name' => 'Password Clients', '--redirect_uri' => config('app.url'), '--user_id' => NULL, '--provider' => 'users' ]);

        CoreMedia::enableRoutes();

        config()->set('auth.guards.api.driver', 'passport');
        config()->set('auth.providers.users.model', MediaUser::class);

        $this->resolveUserFactory()->createOne([
            'email' => 'admin@test.com',
        ]);

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app[ 'router' ];

        $router->post('api/oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')
               ->name('api.oauth.passport.token');

        $router->put('backend/v1/update', function (Request $request, MediaUserRepository $repository) {
            $repository->setThisModel($user = $request->user())
                       ->attachFile($request->avatar_id, $user->avatar, 'avatar');

            return app('kit.response')->resource(new MediaResource(Media::first()));
        })
               ->name('backend.update')
               ->middleware([ 'api', 'auth:api' ]);

        $router->put('backend/v1/update-files', function (Request $request, MediaUserRepository $repository) {
            $repository->setThisModel($user = $request->user())->attachFiles($request->files_ids, 'files');

            return app('kit.response')->resource(MediaResource::collection(Media::orderBy('order')->get()));
        })
               ->name('backend.update-files')
               ->middleware([ 'api', 'auth:api' ]);
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            PostmanServiceProvider::class,
            PassportServiceProvider::class,
            CoreControllerServiceProvider::class,
            CoreMediaServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'MediaStorage' => MediaStorage::class,
        ];
    }

    /**
     * Resolve user class
     *
     * @return Model
     */
    public function resolveUser() : Model
    {
        return new User();
    }

    /**
     * Resolve user class
     *
     * @return Factory
     */
    public function resolveUserFactory() : Factory
    {
        return UserFactory::new();
    }

    /**
     * Return data for default admin creds.
     *
     * @return string[]
     */
    protected function getDefaultAdminCredentials()
    {
        return [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ];
    }

}

class MediaUser extends CoreUser implements HasMedia
{
    use HasApiTokens, ImplementMedia;

    protected $table = 'users';

    public function avatar()
    {
        return $this->morphOne(Media::class, 'model')->where('media_type_in_model', 'avatar');
    }

    public function files()
    {
        return $this->morphMany(Media::class, 'model')
                    ->where('media_type_in_model', 'files')
                    ->orderBy('order');
    }

}

class MediaUserRepository extends MediaRepository implements MediaInteractable
{
    use MediaInteractions;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return \Tests\MediaUser::class;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tests\MediaUserRepository
     */
    public function setThisModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

}