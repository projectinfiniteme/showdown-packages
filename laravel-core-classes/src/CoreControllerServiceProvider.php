<?php

namespace AttractCores\LaravelCoreClasses;

use AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

/**
 * Class CoreControllerServiceProvider
 *
 * @package AttractCores\LaravelCoreClasses
 * Date: 10.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreControllerServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //JsonResource::withoutWrapping();
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kit-core-controller.php', 'kit-core-controller');

        $this->app->bind('kit.response', function () {
            return new ServerResponse();
        });

        Request::macro('routeValue', function(string $key){
            $value = $this->route($key);

            if ($value instanceof Model) {
                return $value->getRouteKey();
            }

            return $value;
        });

        Request::macro('routeModel', function(string $key, string $model, bool $storeParameter = true){
            $value = $this->route($key);

            if ( ! $value instanceof Model) {
                $model = app($model);

                $value = $model->where($model->getRouteKeyName(), $value)->firstOrFail();
            }

            if($storeParameter && $route = call_user_func($this->getRouteResolver())){
                $route->setParameter($key, $value);
            }

            return $value;
        });
    }

}