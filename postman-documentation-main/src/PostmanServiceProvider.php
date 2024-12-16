<?php

namespace AttractCores\PostmanDocumentation;

use AttractCores\PostmanDocumentation\Command\ExportPostmanCollection;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\ServiceProvider;

/**
 * Class PostmanServiceProvider
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 22.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PostmanServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ( $this->app->runningInConsole() ) {
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
        $this->app->singleton('markdown.docs', function () {
            return new MarkdownDocs();
        });

        $this->app->bind(ResourceRegistrar::class, PostmanResourceRegistrar::class);

        Postman::initialize();

        $this->mergeConfigurations();

        $this->commands([ ExportPostmanCollection::class ]);
    }


    /**
     * Merge Kit configuration files.
     */
    protected function mergeConfigurations()
    {
        $path = __DIR__ . '/../config/';
        $this->mergeConfigFrom($path . 'postman.php', 'postman');
    }

    /**
     * Boot configuration publications.
     */
    protected function publishConfigurations()
    {

        $this->publishes([
            __DIR__ . '/../config/postman.php' => config_path('postman.php'),
        ], 'attract-core-portman-documentation-config');
    }

}