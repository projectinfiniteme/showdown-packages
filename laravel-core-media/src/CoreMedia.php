<?php

namespace AttractCores\LaravelCoreMedia;

use AttractCores\PostmanDocumentation\Facade\Markdown;
use Illuminate\Support\Facades\Route;

/**
 * Class CoreMedia
 *
 * @package AttractCores\LaravelCoreMedia
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreMedia
{

    /**
     * Enable api routes
     *
     * @param string   $apiPrefix
     * @param string   $apiPrefixName
     * @param string[] $middlewares
     */
    public static function enableApiRoutes($apiPrefix = 'api/v1', $apiPrefixName = 'api.v1.', $middlewares = [ 'api', 'auth:api', 'check-scopes:api', 'verified:api' ])
    {
        static::addRoutes($apiPrefix, $apiPrefixName, $middlewares);
    }

    /**
     * Enable api routes
     *
     * @param string   $apiPrefix
     * @param string   $apiPrefixName
     * @param string[] $middlewares
     */
    public static function enableBackendRoutes($apiPrefix = 'backend/v1', $apiPrefixName = 'backend.v1.', $middlewares = [ 'api', 'auth:api', 'check-scopes:backend', 'verified:api' ])
    {
        static::addRoutes($apiPrefix, $apiPrefixName, $middlewares);
    }

    /**
     * Enable core module routes.
     */
    public static function enableRoutes()
    {
        static::enableApiRoutes();
        static::enableBackendRoutes();
    }

    /**
     * Add routes.
     *
     * @param       $prefix
     * @param       $prefixName
     * @param array $middlewares
     */
    public static function addRoutes($prefix, $prefixName, $middlewares = [])
    {
        Route::prefix($prefix)
             ->as($prefixName . 'media.')
             ->middleware($middlewares)
             ->namespace("AttractCores\LaravelCoreMedia\Http\Controllers")
             ->group(function () {
                 // Server storage
                 if ( config('kit-media.routes_availability.local') ) {
                     Route::post('local/upload', 'MediaController@store')
                          ->name('local.upload')
                          ->middleware('throttle:kit-media')
                          ->aliasedName('Upload media locally')
                          ->structureDepth(3)
                          ->description(
                              Markdown::heading('How to implement?')
                                      ->numericList([
                                          'Add `multipart/form-data` header into request',
                                          'Put media data into body parameter called `file`',
                                      ])
                          );
                 }

                 Route::prefix('s3')
                      ->as('s3.')
                      ->middleware([ 'throttle:kit-media' ])
                      ->group(function () {
                          // S3 storage
                          if ( config('kit-media.routes_availability.s3-store') ) {
                              Route::post('upload', 'MediaController@storeS3')
                                   ->name('upload')
                                   ->structureDepth(3)
                                   ->aliasedName('Store S3 uploaded media on server');
                          }

                          // Presigned routes.
                          if ( config('kit-media.routes_availability.s3-signed-upload') ) {
                              Route::post('pre-signed', 'S3PreSignController@handleForSources')
                                   ->name('pre-signed')
                                   ->structureDepth(3)
                                   ->aliasedName('Request S3 pre-signed url for media upload');
                          }

                          if ( config('kit-media.routes_availability.s3-signed-source') ) {
                              Route::get('source-link', 'S3PreSignController@getSourceLink')
                                   ->name('source-link')
                                   ->structureDepth(3)
                                   ->aliasedName('Request S3 pre-signed url for uploaded media to show it up');
                          }
                      });
             });
    }

}