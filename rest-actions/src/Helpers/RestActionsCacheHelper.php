<?php

namespace Amondar\RestActions\Helpers;

use Illuminate\Http\Request;

/**
 * Trait RestActionsCacheHelper
 *
 * @version 1.0.0
 * @date    03/02/2020
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsCacheHelper
{

    /**
     * Default cache key prefix.
     *
     * @var string
     */
    protected $cacheKeyPrefix = 'rest.api';

    /**
     * Check if action need to be cached.
     *
     * @return bool
     */
    public function restActionsCacheNeeded()
    {
        $action = $this->currentAction();

        return $action && $action[ 'cache' ][ 'time' ] && ! empty($action[ 'cache' ][ 'tags' ]) ? $action[ 'cache' ] : false;
    }

    /**
     * @param Request $request
     * @param array   $parameters
     * @param null    $id
     *
     * @return mixed
     */
    protected function getRestActionsCache(Request $request, array $parameters = [], $id = NULL)
    {
        // If the query is requested to be cached, we will cache it using a unique key
        // for this model and query statement, including the all parts
        // that are used on this query, providing great convenience when caching.
        [ $key, $time ] = $this->getRestActionsCacheInfo($request);

        $cache = $this->restActionsCacheInstance();

        if ( ! $id ) {
            $callback = $this->getRestActionsCacheCallback('paginate', $request, $parameters);
        } else {
            $callback = $this->getRestActionsCacheCallback('getItem', $id, $request, $parameters);
        }

        // If the "minutes" value is less than zero, we will use that as the indicator
        // that the value should be remembered indefinitely and if we have minutes
        // we will use the typical remember function here.
        if ( $time < 0 ) {
            return $cache->rememberForever($key, $callback);
        }

        return $cache->remember($key, $time, $callback);
    }

    /**
     * Get the cache key and cache minutes as an array.
     *
     * @param Request $request
     * @param array   $parameters
     *
     * @return array
     */
    protected function getRestActionsCacheInfo(Request $request, array $parameters = [])
    {
        return [
            $this->generateRestActionsCacheKey($request, $parameters), $this->currentAction()[ 'cache' ][ 'time' ],
        ];
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @param Request $request
     * @param array   $parameters
     *
     * @return string
     */
    protected function generateRestActionsCacheKey(Request $request, array $parameters = [])
    {
        $name = sprintf("%s.%s.%s", $this->cacheKeyPrefix, $this->getRestModelRouteKeyName(), $this->actionName);

        return hash('sha256', $name . $request->path() . $request->getQueryString() . serialize($request->all()) . serialize($parameters));
    }

    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function restActionsCacheInstance()
    {
        $cache = app('cache');
        $tags = $this->currentAction()[ 'cache' ][ 'tags' ];

        return $tags ? $cache->tags($tags) : $cache;
    }

    /**
     * Get the Closure callback used when caching queries.
     *
     * @param       $realFunction
     * @param array $arguments
     *
     * @return \Closure
     */
    protected function getRestActionsCacheCallback($realFunction, ...$arguments)
    {
        return function () use ($realFunction, $arguments) {
            $this->action[ 'cache' ] = [
                'time' => false,
                'tag'  => NULL,
            ];

            return call_user_func_array([ $this, $realFunction ], $arguments);
        };
    }

}