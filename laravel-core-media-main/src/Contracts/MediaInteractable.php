<?php

namespace AttractCores\LaravelCoreMedia\Contracts;

use AttractCores\LaravelCoreMedia\Models\Media;

/**
 * Interface MediaInteractable
 *
 * @package AttractCores\LaravelCoreMedia\Contracts
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface MediaInteractable
{

    /**
     * Attach media to model
     *
     * @param array  $mediaIds
     * @param        $modelRelation
     */
    public function attachFiles(array $mediaIds, $modelRelation) : void;


    /**
     * Attach media to model
     *
     * @param             $mediaId
     * @param Media|null  $currentModelValue
     * @param             $modelRelation
     * @param int         $order
     * @param bool        $hasMany - Determine that relation should be loaded inside or outside the function.
     */
    public function attachFile($mediaId, ?Media $currentModelValue, $modelRelation, int $order = 1, $hasMany = false) : void;

}