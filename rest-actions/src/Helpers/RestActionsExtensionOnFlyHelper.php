<?php

namespace Amondar\RestActions\Helpers;

/**
 * Trait RestActionsExtensionOnFlyHelper
 *
 * @version 1.0.0
 * @date    11.04.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsExtensionOnFlyHelper
{

    /**
     * @param $action
     * @param $viewName
     *
     * @return $this
     */
    public function setActionView($action, $viewName)
    {
        $this->actions[ $action ][ 'view' ] = $viewName;

        return $this;
    }

    /**
     * @param $action
     * @param $methodName
     *
     * @return $this
     */
    public function setActionRepositoryMethod($action, $methodName)
    {
        $this->actions[ $action ][ 'repository' ] = $methodName;

        return $this;
    }

    /**
     * @param       $action
     * @param array $extendedConditions
     *
     * @return $this
     */
    public function setActionConditions($action, array $extendedConditions)
    {
        $conditions = isset($this->actions[ $action ][ 'conditions' ]) ? $this->actions[ $action ][ 'conditions' ] : [];
        $this->actions[ $action ][ 'conditions' ] = array_merge($conditions, $extendedConditions);

        return $this;
    }

    /**
     * @param       $action
     * @param array $extendedParameters
     *
     * @return $this
     */
    public function setActionParameters($action, array $extendedParameters)
    {
        $conditions = isset($this->actions[ $action ][ 'parameters' ]) ? $this->actions[ $action ][ 'parameters' ] : [];
        $this->actions[ $action ][ 'parameters' ] = array_merge($conditions, $extendedParameters);

        return $this;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function disableAjaxTransformation($action)
    {
        $this->actions[ $action ][ 'ajaxTransform' ] = false;

        return $this;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function disableViewDataOnLoad($action)
    {
        $this->actions[ $action ][ 'simpleView' ] = true;

        return $this;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function enableOnlyAjaxQueries($action)
    {
        $this->actions[ $action ][ 'onlyAjax' ] = true;

        return $this;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function enableOnlyBrowserQueries($action)
    {
        $this->actions[ $action ][ 'onlyBrowser' ] = true;

        return $this;
    }

    /**
     * @param $action
     *
     * @param $value
     *
     * @return $this
     */
    public function enableCaching($action, $value)
    {
        $this->actions[ $action ][ 'cache' ] = $value;

        return $this;
    }

}