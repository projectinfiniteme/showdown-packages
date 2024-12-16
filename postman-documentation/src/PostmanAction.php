<?php

namespace AttractCores\PostmanDocumentation;

use AttractCores\PostmanDocumentation\Macros\RouteCallbacks;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class PostmanAction
 *
 * @method mixed getAction( $key = NULL ) - Get the action array or one of its properties for the route.
 * @method PostmanAction aliasedName( string $name ) - Set aliased name to route.
 * @method string|NULL getAliasedName() - Return aliased name of the route.
 * @method PostmanAction structureDepth( int $depth ) - Set structure depth of name dot parts for structured generation..
 * @method int|NULL getStructureDepth() - Return structure depth marker.
 * @method PostmanAction expands( string $modelClass, array $description = [] ) - Set expands documentation generation to given
 *         model class with given descriptions.
 * @method PostmanAction scopes( string $modelClass, array $description = [] ) - Set scopes documentation generation to given
 *         model class with given descriptions.
 * @method PostmanAction description( MarkdownDocs $docs ) - Set given markdown documentation for current route.
 * @method PostmanAction docPattern( string $pattern ) - Set pattern of doc generation for current route. For example:
 *         expands|description|scopes. By default will be used - description|expands|scopes.
 * @method string compileDocs() - Return compiled markdown docs for current route.
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 10.01.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PostmanAction implements Arrayable
{
    use RouteCallbacks;

    /**
     * Contains all extended action data.
     *
     * @var array
     */
    protected array $action = [];

    /**
     * Magic.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        $newName = $name . 'Callback';
        $reflectionMethod = new \ReflectionMethod($this, $newName);

        if($reflectionMethod->isStatic()){
            $callback = call_user_func([$this, $newName]);
            $callback = $callback->bindTo($this, static::class);

            return $callback(...$arguments);
        }

        throw new \BadMethodCallException("Method $name does not exist in the class.");
    }

    /**
     * Return fresh instance of action extension.
     *
     * @return static
     */
    public static function fresh()
    {
        return new static;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->action;
    }

}