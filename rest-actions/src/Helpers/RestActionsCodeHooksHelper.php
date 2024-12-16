<?php

namespace Amondar\RestActions\Helpers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;

/**
 * Trait RestActionsCodeHooksHelper
 *
 * @version 1.0.0
 * @date    02.08.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsCodeHooksHelper
{

    /**
     * Call rest api action hook
     *
     * @param         $name
     * @param Request $request
     * @param Router  $router
     *
     * @return mixed
     */
    public function callRestApiActionHook($name, Request $request, Router $router)
    {
        if ( \Str::contains($name, 'extend') ) {
            return $this->callRestApiExtension(
                $name,
                [ $request, $router ]
            );
        }

        return $this->callRestApiExtension(
            $name,
            [ $request, $router ],
            $this->getDefaultActionParameters($request, $router)
        );
    }

    /**
     * Call api extension function.
     *
     * @param       $name
     * @param       $parameters
     * @param array $defaultResponse
     *
     * @return mixed
     */
    protected function callRestApiExtension($name, $parameters, $defaultResponse = [])
    {
        if ( method_exists($this, $name) ) {
            return call_user_func_array([ $this, $name ], $parameters);
        }

        return $defaultResponse;
    }

}