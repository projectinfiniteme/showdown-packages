<?php

namespace AttractCores\LaravelCoreClasses\Casters;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Class AlwaysJson
 *
 * @package ${NAMESPACE}
 * Date: 03.02.2022
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class JsonAlways implements CastsAttributes
{

    /**
     * Type of transformation.
     *
     * @var string
     */
    protected string $type;

    /**
     * AlwaysJson constructor.
     *
     * @param string $type
     */
    public function __construct(string $type = 'object')
    {
        $this->type = $type;
    }


    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $value = $value ?? [];

        if ( is_string($value) ) {
            return json_decode($value, $this->type == 'array');
        }

        return $this->type == 'array' ? $value : (object) $value;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value ? json_encode($value) : $value;
    }

}