<?php

namespace AttractCores\LaravelCoreClasses\Extensions;

use Illuminate\Support\Str;

/**
 * Trait HasUUID
 *
 * @package App\Traits\Models
 * Date: 12.02.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
trait HasUUID
{

    /**
     * @return bool
     */
    public function isIncrementing() : bool
    {
        return false;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->isIncrementing();
    }

    /**
     * @return string
     */
    public function getKeyType() : string
    {
        return 'string';
    }

    /**
     * Boot model class.
     */
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            if ( ! $model->{$keyName = $model->getKeyName()} ) {
                $model->$keyName = (string) Str::uuid();
            }
        });
    }

}
