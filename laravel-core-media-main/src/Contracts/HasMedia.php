<?php

namespace AttractCores\LaravelCoreMedia\Contracts;

use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Support\Collection;

/**
 * Interface HasMedia
 *
 * @version 1.0.0
 * @date    04.10.2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
interface HasMedia
{

    /**
     * Return relative path to the media or media for  current model.
     *
     * @param null $mediaName
     *
     * @return string
     */
    public function relativeMediaPath($mediaName = null): string;

    /**
     * Call when we need to remove old medias.
     *
     * @param Media       $media
     * @param string|null $modelRelation
     * @param int         $order
     * @param bool        $hasMany - Determine that relation should be loaded inside or outside the function.
     *
     * @return void
     */
    public function saveMedia(Media $media, $modelRelation = null, int $order = 1, $hasMany = false): void;

    /**
     * Call when we need to remove old medias.
     *
     * @param Collection $mediasForDestroy
     *
     * @return void
     */
    public function removeOldMedia(Collection $mediasForDestroy): void;

    /**
     * Fire event that media removed.
     *
     * @param $media
     */
    public function fireMediaRemovedEvent($media) : void;

    /**
     * Set the given relationship on the model.
     *
     * @param  string  $relation
     * @param  mixed  $value
     * @return $this
     */
    public function setRelation($relation, $value);

    /**
     * Unset a loaded relationship.
     *
     * @param  string  $relation
     * @return $this
     */
    public function unsetRelation($relation);
}