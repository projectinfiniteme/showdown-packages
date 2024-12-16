<?php

namespace AttractCores\PostmanDocumentation\Macros;

use AttractCores\PostmanDocumentation\PostmanAction;

/**
 * Trait RouteResourceCallbacks
 *
 * @package AttractCores\PostmanDocumentation\Macros
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait RouteResourceCallbacks
{

    /**
     * Return callback for ->postman(['index' => PostmanAction::fresh()->aliasedName('Some name')]) route resources fns.
     *
     * @return \Closure
     */
    public static function postmanCallback() : \Closure
    {
        return function (array $ext) {
            foreach ( $ext as $key => $item ) {
                if($item instanceof PostmanAction){
                    $ext[$key] = $item->toArray();
                }
            }

            $this->options[ 'postman' ] = $ext;

            return $this;
        };
    }
}