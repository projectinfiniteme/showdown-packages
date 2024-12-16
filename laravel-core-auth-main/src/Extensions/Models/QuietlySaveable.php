<?php

namespace AttractCores\LaravelCoreAuth\Extensions\Models;

/**
 * Trait QuietlySaveable
 *
 * @version 1.0.0
 * @date    2019-07-29
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait QuietlySaveable
{

    /**
     * Save model without firing events.
     * Use it to update models inside jobs or observers, when you need silent update without new observeable method
     * call.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function saveQuietly(array $options = [])
    {
        return static::withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }
}