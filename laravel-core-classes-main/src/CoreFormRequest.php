<?php

namespace AttractCores\LaravelCoreClasses;

use Amondar\SecurityRequest\SecurityRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CoreFormRequest
 *
 * @package AttractCores\LaravelCoreClasses
 * Date: 04.03.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreFormRequest extends FormRequest
{

    use SecurityRequest;
}