<?php

namespace AttractCores\LaravelCoreClasses\Extensions;

/**
 * Trait ExtendedRules
 * Helper class for requests classes extensions.
 *
 * @package AttractCores\LaravelCoreClasses\Extensions
 * Date: 22.04.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait ExtendedRules
{
    /**
     * Closure to extend rules
     *
     * @var \Closure|null
     */
    private static ?\Closure $extendRules = NULL;

    /**
     * Set callback for rules extension.
     *
     * @param \Closure $closure
     */
    public static function setRulesExtensionCallback(\Closure $closure)
    {
        self::$extendRules = $closure;
    }

    /**
     * Return extended rules.
     *
     * @return mixed
     */
    protected function getExtendedRules()
    {
        if(self::$extendRules){
            return self::$extendRules->call($this);
        }

        return self::getDefaultExtendedRules();
    }

    /**
     * Return default extended rules.
     *
     * @return array
     */
    protected function getDefaultExtendedRules()
    {
        return [];
    }
}