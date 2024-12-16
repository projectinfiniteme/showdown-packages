<?php

namespace AttractCores\LaravelCoreClasses\Extensions;

/**
 * Trait EventLocalization
 *
 * @package App\Events\Extensions
 * Date: 12.04.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait EventLocalization
{
    /**
     * Event locale.
     *
     * @var string
     */
    public string $locale;

    /**
     * Initialize localization for event.
     *
     * @return $this
     */
    public function initializeLocale()
    {
        $this->locale = app()->getLocale();

        return $this;
    }
}
