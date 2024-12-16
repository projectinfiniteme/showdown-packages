<?php

namespace AttractCores\PostmanDocumentation;

use Illuminate\Routing\ResourceRegistrar;

/**
 * Class PostmanResourceRegistrar
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PostmanResourceRegistrar extends ResourceRegistrar
{


    /**
     * Get the action array for a resource route.
     *
     * @param string $resource
     * @param string $controller
     * @param string $method
     * @param array  $options
     *
     * @return array
     */
    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $action = parent::getResourceAction($resource, $controller, $method, $options);

        if ( isset($options[ 'postman' ][ $method ]) ) {
            return array_merge($action, $options[ 'postman' ][ $method ]);
        }

        return $action;
    }

}