<?php

namespace AttractCores\LaravelCoreMedia\Rules;

use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MediaExists
 *
 * @package AttractCores\LaravelCoreMedia\Rules
 * Date: 22.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MediaExists implements Rule
{

    /**
     * Check that we should skip checks on model
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected ?Model $skipOn = NULL;

    /**
     * MediaExists constructor.
     */
    public function __construct()
    {
        //
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Given media does not exists in our db or does not attachable.');
    }

    /**
     * Check the rule.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @internal param array $parameters
     *
     */
    public function passes($attribute, $value)
    {
        /** @var Media $media */
        $media = Media::find($value);

        // Check that media exists and it's attachable.
        if ( ! $media || ( is_null($this->skipOn) && ! is_null($media->model_id) ) ) {
            return false;
        }

        return is_null($media->model_id) || (
                $this->skipOn &&
                $media->model_id == $this->skipOn->getKey() &&
                $media->model_type == get_class($this->skipOn)
            );
    }

    /**
     * Skip validation on given model.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     *
     * @return $this
     */
    public function skipOn(?Model $model)
    {
        $this->skipOn = $model;

        return $this;
    }

}