<?php

namespace Amondar\RestActions\Helpers;

use Amondar\RestActions\Exceptions\RestActionsMethodNotAllowed;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\ErrorHandler\Exception\FatalThrowableError;
use Throwable;

/**
 * Trait RestActionsRepositoryHelper
 *
 * @version 1.0.0
 * @date    03.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsRepositoryHelper
{

    protected $repository;

    /**
     * Call repository action with reflection parameters compile.
     *
     * @param Router  $router
     * @param Request $request
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function runRepositoryAction(Router $router, Request $request)
    {
        if ( $this->canRunRepositoryAction($router) ) {
            $parameters = $this->getActionParameters($router);
            $action = $this->getRepositoryAction($router);

            return call_user_func_array([
                $this->repository, $action,
            ], $this->compileRepositoryActionParameters($action, $parameters, $request));
        }

        throw new RestActionsMethodNotAllowed(500,
            "Rest actions method \"{$this->getActionName($router)}\" not allowed in repository or repository is not declared or repository is not a class.");

    }

    /**
     * @param Router $router
     *
     * @return bool
     */
    public function canRunRepositoryAction(Router $router)
    {
        return (
            $this->repository &&
            method_exists($this->repository, $this->getRepositoryAction($router))
        );
    }

    /**
     * Return expected repository action.
     *
     * @param Router $router
     *
     * @return mixed
     */
    protected function getRepositoryAction(Router $router)
    {
        $action = $this->getAction($router);
        if ( isset($action[ 'repository' ]) && is_string($action[ 'repository' ]) ) {
            return $action[ 'repository' ];
        }

        return $this->getActionName($router);
    }

    /**
     * Compile parameters to repository function by parameters.
     *
     * @param            $action
     * @param Collection $parameters
     * @param Request    $request
     *
     * @return array
     * @throws \ReflectionException
     */
    public function compileRepositoryActionParameters($action, Collection $parameters, Request $request)
    {
        $compiled = [];
        $isRepositoryModelClassUsed = false;
        $repositoryModel = method_exists($this->repository, 'getModel') ? $this->repository->getModel() : NULL;
        $reflection = new \ReflectionMethod($this->repository, $action);
        foreach ( $reflection->getParameters() as $key => $item ) {
            $parameterName = $item->getName();
            if ( $class = $item->getClass() ) { // parameter has class
                $className = $class->name;
                if (
                    $request instanceof $className
                ) { // parameter is Request class or instance of extended request class.
                    $compiled[ $item->getPosition() ] = $request;
                } elseif (
                    ( $data = $this->tryToFoundRepositoryParameter($parameters, $className, $parameterName) ) !== false
                ) { // parameter exists in repository
                    if (
                        class_exists($data[ 'class' ]) && ( $model = app($data[ 'class' ]) ) instanceof Model
                    ) { // is it valid model
                        $compiled[ $item->getPosition() ] = $this->getModelInstanceFromRoute($request, $data[ 'route' ],
                            $model);
                        // Check that we used repository target model without inline set.
                        if ( $repositoryModel ) {
                            $isRepositoryModelClassUsed = $model instanceof $repositoryModel;
                        }
                    }
                }
            } elseif ( $item->isArray() && $parameterName == 'validated' && $request instanceof FormRequest ) {
                $compiled[ $item->getPosition() ] = $request->validated();
            } elseif ( $parameters->has($parameterName) ) { // parameter exists in our parameters by it own name.
                $compiled[ $item->getPosition() ] = $request->route($parameters->get($parameterName)) ??
                                                    $parameters->get($parameterName);
            }

            // Try to remove used parameter from parameters collection.
            $parameters->pull($parameterName);
        }

        // Try to found inline model binding for repository action.
        if (
            ! $isRepositoryModelClassUsed && // We didn't set model yet.
            $request->route($route = $this->getRestModelRouteKeyName()) && // Model exists inside the route
            method_exists($this->repository, 'model') &&
            method_exists($this->repository, 'setModel') // Repository has needed functions.
        ) {
            $this->repository->setModel($this->getModelInstanceFromRoute($request, $route, NULL));
        }

        return $compiled;
    }

    /**
     * Determine repository parameter.
     *
     * @param Collection  $parameters
     * @param string      $reflectionClassName
     * @param string|NULL $reflectionParameterName
     *
     * @return array|bool
     */
    protected function tryToFoundRepositoryParameter(Collection $parameters, string $reflectionClassName, ?string $reflectionParameterName)
    {
        foreach ( $parameters as $parameterName => $class ) {
            $route = $parameterName;

            if ( is_array($class) ) {
                $parameterName = $class[ 'parameter_name' ] ?? $parameterName;
                $class = $class[ 'class' ]; // Class is required to determine
            }

            if (
                $class == $reflectionClassName || $parameterName == $reflectionParameterName ||
                Str::camel($parameterName) == $reflectionParameterName
            ) {
                return [ 'class' => $class, 'parameter_name' => $parameterName, 'route' => $route ];
            }
        }

        return false;
    }

    /**
     * Return valid route instance for model.
     *
     * @param Request $request
     * @param         $identifier
     * @param null    $model
     *
     * @return Model
     */
    protected function getModelInstanceFromRoute(Request $request, $identifier, $model = NULL)
    {
        $routeData = $request->route($identifier);
        if ( $routeData instanceof Model ) { // rote value is bind in service provider or anywhere
            return $routeData;
        } elseif ( $routeData ) {
            // no we must find it in model or abort 404.

            if ( ! $model || ! ( $model instanceof Model ) ) {
                $model = $this->restMakeModel();
            }

            $routeData = $this->getModelForActions($routeData, $model, $request);
            app('router')->current()->setParameter($identifier, $routeData);

            return $routeData;
        }

        return NULL;
    }

    /**
     * Return model for actions.
     *
     * TODO redeclare this function and append more secure db request if needed.
     *
     * @param         $id
     * @param Model   $model
     * @param Request $request
     *
     * @return Model
     */
    protected function getModelForActions($id, Model $model, Request $request)
    {
        // Check if we receive current REST model.
        if ( $model->is($this->restMakeModel()) ) {
            $query = $this->getBaseFilterQuery($request);
        } else {
            $query = $model->newQuery();
        }

        return $query->where($model->getRouteKeyName(), $id)->firstOrFail();
    }

    /**
     * Report api exceptions to log. Move error forward through default laravel error resolver.
     *
     * @param $exception
     */
    protected function restApiActionsReportException($exception)
    {
        if (
            $exception instanceof Throwable &&
            ! $exception instanceof Exception
        ) {
            $exception = new FatalThrowableError($exception);
        }

        app(ExceptionHandler::class)->report($exception);
    }

}
