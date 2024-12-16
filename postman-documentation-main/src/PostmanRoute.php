<?php

namespace AttractCores\PostmanDocumentation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class PostmanRoute
 *
 * @method string|NULL getName() - Return name of the route.
 * @method string uri() - Return uri of the route.
 * @method mixed getAction( $key = NULL ) - Get the action array or one of its properties for the route.
 * @method Route aliasedName( string $name ) - Set aliased name to route.
 * @method string|NULL getAliasedName() - Return aliased name of the route.
 * @method Route structureDepth( int $depth ) - Set structure depth of name dot parts for structured generation..
 * @method int|NULL getStructureDepth() - Return structure depth marker.
 * @method Route expands( string $modelClass, array $description = [] ) - Set expands documentation generation to given
 *         model class with given descriptions.
 * @method Route scopes( string $modelClass, array $description = [] ) - Set scopes documentation generation to given
 *         model class with given descriptions.
 * @method Route description( MarkdownDocs $docs ) - Set given markdown documentation for current route.
 * @method Route docPattern( string $pattern ) - Set pattern of doc generation for current route. For example:
 *         expands|description|scopes. By default will be used - description|expands|scopes.
 * @method string compileDocs() - Return compiled markdown docs for current route.
 *
 * @see     Route
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 01.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PostmanRoute
{
    /**
     * Laravel route class reference.
     *
     * @var \Illuminate\Routing\Route
     */
    protected Route $route;

    /**
     * Determine that route checks passed.
     *
     * @var bool
     */
    protected bool $checksPassed = false;

    /**
     * Array of route methods.
     *
     * @var array
     */
    protected array $methods;

    /**
     * Collection of route middlewares.
     *
     * @var Collection
     */
    protected Collection $middlewares;

    /**
     * Route reflection method.
     *
     * @var \ReflectionMethod|\ReflectionFunction|NULL|object
     */
    protected ?object $reflectionMethod;

    /**
     * Postman export command config.
     *
     * @var array
     */
    protected array $config;

    /**
     * PostmanRoute constructor.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array                     $config
     *
     * @throws \ReflectionException
     */
    public function __construct(Route $route, array $config)
    {

        $this->route = $route;
        $this->config = $config;

        // Bootstrap the class.
        $this->bootstrap();
    }

    /**
     * Process call function requests.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ( method_exists($this, $name) ) {
            return call_user_func_array([ $this, $name ], $arguments);
        }

        return call_user_func_array([ $this->route, $name ], $arguments);
    }

    /**
     * Determine that route checks passed.
     *
     * @return bool
     */
    public function isChecksPassed() : bool
    {
        return $this->checksPassed;
    }

    /**
     * Return route methods.
     *
     * @return array
     */
    public function methods() : array
    {
        return $this->methods;
    }

    /**
     * Return middlewares of the route.
     *
     * @return \Illuminate\Support\Collection
     */
    public function middlewares() : Collection
    {
        return $this->middlewares;
    }

    /**
     * Return route reflection method.
     *
     * @return object|\ReflectionFunction|\ReflectionMethod|null
     */
    public function reflectionMethod() : ?object
    {
        return $this->reflectionMethod;
    }

    /**
     * Return auth structure in postman format.
     *
     * @param string|null $personalBearer
     *
     * @return array
     */
    public function getRouteAuthPostmanStructure(?string $personalBearer = NULL) : array
    {
        switch ( $type = $this->config[ 'auth_type' ] ) {
            case 'none':
                return [];
            case 'oauth2':
                if ( $personalBearer ) {
                    return [
                        'type'   => 'bearer',
                        'bearer' => [
                            [
                                'key'   => 'token',
                                'value' => $personalBearer,
                                'type'  => 'string',
                            ],
                        ],
                    ];
                } elseif ( $this->middlewares->intersect([ $this->config[ 'auth_middleware' ], $this->config[ 'client_auth_middleware' ] ]) ) {
                    $grantType = $this->middlewares->contains($this->config[ 'auth_middleware' ]) ? 'password' :
                        'client_credentials';

                    $data = $this->getAuthDefaultsByGrant($grantType);

                    $data[ 'oauth2' ] = array_merge($data[ 'oauth2' ], [
                        [
                            'key'   => 'scope',
                            'value' => $this->compileScopesByUri(),
                            'type'  => 'string',
                        ],
                    ]);

                    if ( $grantType == 'password' ) {
                        $data[ 'oauth2' ] = array_merge($data[ 'oauth2' ], $this->getAuthDefaultUserCredentials());
                    }

                    return $data;
                }
                break;
            default:
                return [
                    'type'   => 'apikey',
                    'apikey' => [
                        [
                            'key'   => 'key',
                            'value' => $this->config[ 'auth_type' ],
                            'type'  => 'string',
                        ],
                        [
                            'key'   => 'value',
                            'value' => NULL,
                            'type'  => 'string',
                        ],
                        [
                            "key"   => "in",
                            "value" => $this->config[ 'token_placement' ],
                            "type"  => "string",
                        ],
                    ],
                ];
        }
    }

    /**
     * Return FormRequest class by given reflection
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getRouteFormRequestClass(Router $router) : ?string
    {
        if ( $this->reflectionMethod instanceof ReflectionMethod ) {
            $class = $this->reflectionMethod->getDeclaringClass();

            // Check for RestApi package usage.
            if ( ! $class->isAbstract() && $class->hasMethod('getDefaultAction') ) {
                $instance = app($class->getName());

                // Get RestApi package actions
                $actions = $class->getMethod('getActions')->invoke($instance, $router);

                // Check that we want to invoke RestApi package action
                if ( ! empty($actions[ $methodName = $this->reflectionMethod->getName() ]) ) {
                    // Get Laravel application container
                    $application = app();

                    // Get action value from RestApi package
                    $actionValue = $actions[ $methodName ];

                    // Set getDefaultAction method as accessible.
                    $defaultActionMethod = $class->getMethod('getDefaultAction');
                    $defaultActionMethod->setAccessible(true);
                    $requestClass = collect($defaultActionMethod->invoke($instance))->merge($actionValue)[ 'request' ];
                    $applicationBinding = value(
                        Arr::get($application->getBindings(), $requestClass . '.concrete'),
                        $application
                    );

                    // Get RestApi request for current route action.
                    $parameters = [
                        $applicationBinding ? get_class($applicationBinding) : $requestClass,
                    ];
                }
            }
        }

        // If parameters is empty, then get them by default.
        if ( empty($parameters) ) {
            $parameters = $this->reflectionMethod->getParameters();
        }

        $requestClass = collect($parameters)
            ->filter(function ($value, $key) {
                if ( $value instanceof \ReflectionParameter ) {
                    if ( $type = $value->getType() ) {
                        $value = $type->getName();
                    } else {
                        $value = NULL;
                    }
                }

                return $value && is_subclass_of($value, FormRequest::class);
            })
            ->first();

        return $requestClass instanceof \ReflectionParameter ? $requestClass->getType()->getName() : $requestClass;

    }

    /**
     * Bootstrap the class.
     *
     * @throws \ReflectionException
     */
    protected function bootstrap() : void
    {
        // Gather route middlewares.
        $this->middlewares = collect($this->route->gatherMiddleware());

        if ( $this->checksPassed() ) {
            // Bootstrap methods property.
            $this->methods = array_filter($this->route->methods(), fn($value) => $value !== 'HEAD');

            // Determine callable method reflection.
            $this->reflectionMethod = $this->getReflectionMethod($this->route->getAction());
        }

    }

    /**
     * Determine that route should be processed to postman.
     *
     * @return bool
     */
    protected function checksPassed() : bool
    {
        return $this->checksPassed =
            // Check, that route does not have excluded middlewares
            $this->middlewares->intersect($this->config[ 'exclude_middleware' ])->isEmpty()
            // Also check, that route has intersection with included middlewares
            && $this->middlewares->intersect($this->config[ 'include_middleware' ])->isNotEmpty();
    }

    /**
     * Compile scopes
     *
     * @return string
     */
    protected function compileScopesByUri() : string
    {
        if ( empty($this->config[ 'scopes' ]) ) {
            return '*';
        }

        $scopes = [];

        foreach ( $this->config[ 'scopes' ] as $pattern => $scope ) {
            if ( preg_match($pattern, $this->uri()) ) {
                $scopes [] = $scope;
            }
        }

        return implode(' ', $scopes);
    }

    /**
     * Return defaults by given grant type for oauth2.0
     *
     * @param string $grantType
     *
     * @return array
     */
    protected function getAuthDefaultsByGrant(string $grantType) : array
    {
        return [
            'type'   => 'oauth2',
            'oauth2' => [
                [
                    'key'   => 'accessTokenUrl',
                    'value' => '{{oauth_full_url}}',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'client_authentication',
                    'value' => 'body',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'grant_type',
                    'value' => $grantType == 'password' ? 'password_credentials' : $grantType,
                    'type'  => 'string',
                ],
                [
                    'key'   => 'clientId',
                    'value' => $this->config[ 'auth_clients' ][ $grantType ][ 'id' ],
                    'type'  => 'string',
                ],
                [
                    'key'   => 'clientSecret',
                    'value' => $this->config[ 'auth_clients' ][ $grantType ][ 'secret' ],
                    'type'  => 'string',
                ],
                [
                    'key'   => 'tokenName',
                    'value' => 'Token',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'challengeAlgorithm',
                    'value' => 'S256',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'addTokenTo',
                    'value' => 'header',
                    'type'  => 'string',
                ],
                [
                    'key'   => 'tokenType',
                    'value' => 'Bearer',
                    'type'  => 'string',
                ],
            ],
        ];
    }

    /**
     * Return default user credentials.
     *
     * @return array[]
     */
    protected function getAuthDefaultUserCredentials() : array
    {
        return [
            [
                'key'   => 'password',
                'value' => $this->config[ 'start-user' ][ 'password' ],
                'type'  => 'string',
            ],
            [
                'key'   => 'username',
                'value' => $this->config[ 'start-user' ][ 'email' ],
                'type'  => 'string',
            ],
        ];
    }

    /**
     * @param array $routeAction
     *
     * @return ReflectionMethod|ReflectionFunction|NULL
     * @throws \ReflectionException
     */
    protected function getReflectionMethod(array $routeAction) : ?object
    {
        // Hydrates the closure if it is an instance of Opis\Closure\SerializableClosure
        if ( $this->containsSerializedClosure($routeAction) ) {
            $routeAction[ 'uses' ] = unserialize($routeAction[ 'uses' ])->getClosure();
        }

        if ( $routeAction[ 'uses' ] instanceof Closure ) {
            return new ReflectionFunction($routeAction[ 'uses' ]);
        }

        $routeData = explode('@', $routeAction[ 'uses' ]);

        $reflection = new ReflectionClass($routeData[ 0 ]);

        if ( ! $reflection->hasMethod($routeData[ 1 ]) ) {
            return NULL;
        }

        return $reflection->getMethod($routeData[ 1 ]);
    }

    /**
     * Determine that given action use serialized closure.
     *
     * @param array $action
     *
     * @return bool
     */
    public static function containsSerializedClosure(array $action) : bool
    {
        return is_string($action[ 'uses' ]) &&
               Str::startsWith($action[ 'uses' ], 'C:32:"Opis\\Closure\\SerializableClosure') !== false;
    }

}
