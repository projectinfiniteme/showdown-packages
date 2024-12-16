<?php

namespace AttractCores\PostmanDocumentation\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class Markdown
 *
 * @package AttractCores\PostmanDocumentation
 * Date: 22.12.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class Markdown extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'markdown.docs';
    }
}
