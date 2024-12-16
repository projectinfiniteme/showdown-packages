<?php

namespace Amondar\RestActions\Helpers;


use Illuminate\Contracts\Events\Dispatcher;

/**
 * Trait RestActionsEventsHelper
 *
 * @version 1.0.0
 * @date    05.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsEventsHelper
{

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected static $dispatcher;

    /**
     * @var string
     */
    protected static $eventsPrefix = 'rest.api';

    /**
     * User exposed observable events.
     *
     * @var array
     */
    protected static $observables = [];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native RestActions events.
     *
     * @var array
     */
    protected $dispatchesEvents = [];

    /**
     * Register an observer with the Model.
     *
     * @param object|string $class
     *
     * @return void
     */
    public static function restObserve($class)
    {
        if ( ! static::$dispatcher ) {
            self::$dispatcher = app(Dispatcher::class);
        }

        $className = is_string($class) ? $class : get_class($class);

        // When registering a model observer, we will spin through the possible events
        // and determine if this observer has that method. If it does, we will hook
        // it into the model's event system, making it convenient to watch these.
        foreach ( static::getRestObservableEvents() as $event ) {
            if ( method_exists($class, $event) ) {
                static::registerRestEvent($event, $className . '@' . $event);
            }
        }
    }

    /**
     * Get the observable event names.
     *
     * @return array
     */
    protected static function getRestObservableEvents()
    {
        return array_merge(
            [
                'indexing', 'index', 'showing', 'show',
                'storing', 'stored', 'updating', 'updated',
                'deleting', 'deleted'
            ],
            static::$observables
        );
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param string          $event
     * @param \Closure|string $callback
     *
     * @return void
     */
    protected static function registerRestEvent($event, $callback)
    {
        $name = static::class;
        $eventName = static::$eventsPrefix . ".{$event}: {$name}";

        if ( isset(static::$dispatcher) && ! static::$dispatcher->hasListeners($eventName) ) {
            static::$dispatcher->listen($eventName, $callback);
        }
    }

    /**
     * Enable controller events dispatching.
     */
    protected function enableDispatching()
    {
        self::$dispatcher = app(Dispatcher::class);
    }

    /**
     * Fire the given event for the model.
     *
     * @param string $event
     * @param null   $item
     * @param bool   $halt
     *
     * @return mixed
     */
    protected function fireRestEvent($event, $item = NULL, $halt = true)
    {
        if ( ! isset(static::$dispatcher) ) {
            return true;
        }

        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event. If that
        // returns a result we can return that result, or we'll call the string events.
        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterRestActionsEventResults(
            $this->fireCustomRestActionEvent($event, $method, $item)
        );

        if ( $result === false ) {
            return false;
        }

        return ! empty($result) ? $result :
            static::$dispatcher->$method(
                static::$eventsPrefix . ".{$event}: " . static::class,
                $item
            );
    }

    /**
     * Filter the Rest Actions event results.
     *
     * @param mixed $result
     *
     * @return mixed
     */
    protected function filterRestActionsEventResults($result)
    {
        if ( is_array($result) ) {
            $result = array_filter($result, function ($response) {
                return ! is_null($response);
            });
        }

        return $result;
    }

    /**
     * Fire a custom Rest Action event for the given event.
     *
     * @param string $event
     * @param string $method
     * @param        $item
     *
     * @return mixed|null
     */
    protected function fireCustomRestActionEvent($event, $method, $item)
    {
        if ( ! isset($this->dispatchesEvents[ $event ]) ) {
            return;
        }

        $result = static::$dispatcher->$method(new $this->dispatchesEvents[ $event ]($item));

        if ( ! is_null($result) ) {
            return $result;
        }
    }

//    /**
//     * Handle dynamic static method calls into the method.
//     *
//     * @param  string  $method
//     * @param  array  $parameters
//     * @return mixed
//     */
//    public static function __callStatic($method, $parameters)
//    {
//        return (new static)->$method(...$parameters);
//    }
}