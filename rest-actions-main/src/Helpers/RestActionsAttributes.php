<?php

namespace Amondar\RestActions\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait RestActionsAttributes
 *
 * @version 1.0.0
 * @date    2019-07-25
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsAttributes
{

    protected static $PROTECTED_LIMIT = 100;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * Main request class.
     *
     * @var string
     */
    protected $restActionsRequest = Request::class;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var Collection
     */
    protected $action;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $viewData = [];


    /**
     * получить нужный объект Request для данного действия и контроллера
     *
     * @param Router $router
     *
     * @return Request
     */
    public function getValidationRequest(Router $router)
    {
        return app($this->action[ 'request' ]);
    }

    /**
     * Return collection with action parameters.
     *
     * @param Router $router
     *
     * @return Collection
     */
    public function getActionParameters(Router $router)
    {
        return collect($this->getAction($router)->get('parameters', []));
    }

    /**
     * Return action.
     *
     * @param Router $router
     *
     * @return Collection
     */
    public function getAction(Router $router)
    {

        if ( $action = $this->currentAction() ) {
            return $action;
        }

        $actionName = $this->getActionName($router);
        $instance = collect($this->getDefaultAction());
        $actions = $this->getActions($router);

        if ( in_array($actionName, array_keys($actions)) && ! empty($actions[ $actionName ]) ) {
            $this->action = $instance->merge($actions[ $actionName ]);
        } else {
            $this->action = $instance;
        }

        return $this->action;
    }

    /**
     * Return current action, if you now that action is not empty.
     *
     * @return Collection
     */
    public function currentAction()
    {
        return $this->action;
    }

    /**
     * Get controller action name.
     *
     * @param Router $router
     *
     * @return string
     */
    public function getActionName(Router $router)
    {
        if ( ! $this->actionName ) {
            $this->actionName = explode('@', $router->getCurrentRoute()->getActionName())[ 1 ];
        }

        return $this->actionName;
    }

    /**
     * Default action parameters.
     *
     * @return array
     */
    protected function getDefaultAction()
    {
        return [
            'request'             => $this->restActionsRequest, // Which request should be used by default.
            'model'               => NULL, // Which Model should be used by default. Override all model definitions.
            'view'                => NULL, // Which view should be rendered on browser action.
            'conditions'          => [], // Filter conditions.
            'parameters'          => [], // Route parameters for repository actions.
            'transformer'         => Resource::class, // Which transformer should be used.
            'ajaxTransform'       => true, // Should we use resource transformation or not.
            'simpleView'          => false, // Should we render simple view, without database request.
            'onlyAjax'            => false, // Should action be accessible only for ajax requests.
            'onlyBrowser'         => false, // Should action be accessible only for browser requests.
            'cache'               => [ // Cache settings.
                                       'time' => false,
                                       'tags' => NULL,
            ],
            'protectedLimit'      => static::$PROTECTED_LIMIT,
            // Action protected limit. How much records could be fetched from the db at once. Override limit filter property, if it greater then this value.
            'sextantRestrictions' => [], // Sextant restrictions integration.
        ];
    }

    /**
     * Return actions list.
     *
     * @return array
     */
    public function getActions(Router $router)
    {
        return $this->actions;
    }

    /**
     * Sanitize object class name for key value on returned data array.
     *
     * @param $object
     *
     * @return string
     */
    public function sanitizeClassName($object)
    {
        return \Str::snake(class_basename($object));
    }

    /**
     * Return default action parameters.
     *
     * @param Request $request
     * @param Router  $router
     *
     * @return array
     */
    public function getDefaultActionParameters(Request $request, Router $router)
    {
        return [
            'parameters' => $this->getActionConditions($router)->toArray(),
            // Add other possible extends.
        ];
    }

    /**
     * Return action conditions for filter.
     *
     * @param Router $router
     *
     * @return Collection
     */
    public function getActionConditions(Router $router)
    {
        return collect($this->getAction($router)->get('conditions', []));
    }

    /**
     * Return array of request data.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getValidationRequestData(Request $request)
    {
        if ( $request instanceof FormRequest ) {
            return $request->validated();
        }

        return $request->all();
    }

    /**
     * Return route key of current model
     *
     * @param Request $request
     *
     * @return \Illuminate\Routing\Route|mixed|object|string
     */
    protected function getRestModelRouteValue(Request $request)
    {
        return $request->route($this->getRestModelRouteKeyName());
    }

    /**
     * Return route parameter key name by Model
     *
     * @param null $model
     *
     * @return string
     */
    protected function getRestModelRouteKeyName($model = NULL)
    {
        $model = $model ?? $this->restMakeModel();
        if ( method_exists($model, 'getRestRouteKeyName') ) {
            return $model->getRestRouteKeyName();
        }

        return \Str::snake(class_basename($model));
    }

    /**
     * Make the model
     *
     * @return Model
     */
    protected function restMakeModel()
    {
        if ( $this->model ) {
            return $this->model;
        } elseif ( class_exists($this->modelClass) ) {
            return $this->model = app($this->modelClass);
        } elseif (
            property_exists($this, 'repository') &&
            is_object($this->repository) &&
            method_exists($this->repository, 'getModel')
        ) {
            return $this->model = $this->repository->getModel();
        }

        throw new NotFoundHttpException('Model class was not found in RestActionsFilterHelper');
    }

    /**
     * Manipulate modelClass property to say action - work with simple methods without repository.
     *
     * @param Router $router
     */
    protected function checkActionInlineModelClass(Router $router)
    {
        $action = $this->getAction($router);

        if ( $action[ 'model' ] && class_exists($action[ 'model' ]) ) {
            $this->modelClass = $action[ 'model' ];
        }
    }

}