<?php

namespace AttractCores\LaravelCoreMedia\Extensions;

use AttractCores\LaravelCoreMedia\Events\MediaDeleted;
use AttractCores\LaravelCoreMedia\Jobs\RemoveMediaModelPhysically;
use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait ImplementMedia
 *
 * @version 1.0.0
 * @date    2020-01-20
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait ImplementMedia
{

    /**
     * Return relative path to the media or media for  current model.
     *
     * @param null $mediaName
     *
     * @return string
     */
    public function relativeMediaPath($mediaName = NULL) : string
    {
        return sprintf('%s/%s/%s', Str::snake(class_basename($this)), $this->getKey(), $mediaName);
    }

    /**
     * Call when we need to remove old media.
     *
     * @param Media       $media
     * @param string|null $modelRelation
     * @param int         $order
     * @param bool        $hasMany - Determine that relation should be loaded inside or outside the function.
     *
     * @return void
     */
    public function saveMedia(Media $media, $modelRelation = NULL, int $order = 1, $hasMany = false) : void
    {
        $this->$modelRelation()->save($media->withType($modelRelation)->withOrder($order));

        // Set saved media relation
        if ( ! $hasMany && method_exists($this, $modelRelation) ) {
            $this->setRelation($modelRelation, $media);
        }
    }

    /**
     * Call when we need to remove old media.
     *
     * @param Collection $mediaForDestroy
     *
     * @return void
     * @throws \Exception
     */
    public function removeOldMedia(Collection $mediaForDestroy) : void
    {
        /** @var Media $media */
        foreach ( $mediaForDestroy as $media ) {
            // Soft delete a media to hide it from media list.
            $media->delete();

            $this->fireMediaRemovedEvent($media);
        }
    }

    /**
     * Fire event that media removed.
     *
     * @param Media $media
     */
    public function fireMediaRemovedEvent($media) : void
    {
        event(new MediaDeleted($media));
    }

}
