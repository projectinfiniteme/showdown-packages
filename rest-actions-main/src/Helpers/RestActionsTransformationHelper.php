<?php

namespace Amondar\RestActions\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

/**
 * Trait RestActionsTransformationHelper
 *
 * @version 2.0.0
 * @date    08.05.2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsTransformationHelper
{

    /**
     * Transform collection method.
     *
     * @param Model|Collection $data
     *
     * @return object
     * @throws \ReflectionException
     */
    protected function restActionsTransformData($data)
    {
        // Get transformer class name.
        $transformerClass = $this->getTransformerClass();

        // Get Transformer class instance.
        $transformer = $this->makeResourceTransformer($transformerClass, $data);

        // Determine that our transformer can work with collections by default.
        $isTransformerForCollection = $this->isTransformerForCollection($transformer);

        // We receive a model or our class can work with collections by default.
        if (
            $data instanceof Model || (
                $isTransformerForCollection && (
                    $data instanceof Collection || $data instanceof AbstractPaginator
                )
            )
        ) {
            return $transformer;
        } else {
            return $transformer->collection($data);
        }
    }

    /**
     * @param string           $transformerClass
     * @param Model|Collection $data
     *
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function makeResourceTransformer(string $transformerClass, $data)
    {
        return app($transformerClass, [ 'resource' => $data ]);
    }

    /**
     * Return class of transformer.
     *
     * @return mixed
     */
    protected function getTransformerClass()
    {
        return $this->action[ 'transformer' ];
    }

    /**
     * Check if transformer class for collections.
     *
     * @param $class
     *
     * @return bool
     * @throws \ReflectionException
     */
    protected function isTransformerForCollection($class)
    {
        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->isSubclassOf(ResourceCollection::class);
    }

}