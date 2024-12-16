<?php

namespace AttractCores\LaravelCoreMedia\Extensions;

use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Models\Media;

/**
 * Trait CanAttachFiles
 *
 * @property \AttractCores\LaravelCoreMedia\Contracts\HasMedia $model
 *
 * @version 1.0.0
 * @date    2019-02-20
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait MediaInteractions
{

    /**
     * Attach files to model
     *
     * @param array  $fileIds
     * @param        $modelRelation
     */
    public function attachFiles(array $fileIds, $modelRelation) : void
    {
        if ( ! empty($fileIds) ) {
            /** @var \Illuminate\Support\Collection $currentModelFiles */
            $currentModelFiles = $this->model->$modelRelation;
            $givenFiles = Media::find($fileIds);
            $validMedia = collect([]);

            foreach ( $fileIds as $index => $fileId ) { // save unsaved files.
                $order = $index + 1;

                /** @var Media $file */
                $file = $givenFiles->firstWhere('id', $fileId);

                if ( $file->model_id === NULL ) {
                    $this->attachFile($file, NULL, $modelRelation, $order, true);
                } else { // update file ordering
                    $file->withOrder($order)->save();
                }

                $validMedia->push($file);
            }

            // Set new media collection as model relation.
            // Set saved media relation
            if ( method_exists($this->model, $modelRelation) ) {
                $this->model->setRelation($modelRelation, $validMedia);
            }

            // Remove old files.
            if (
            ( $filesForDeletion = $currentModelFiles->keyBy('id')
                                                    ->diffKeys($validMedia->keyBy('id')) )->count()
            ) {
                $this->model->removeOldMedia($filesForDeletion);
            }
        } elseif ( ! $this->model->$modelRelation->isEmpty() ) {
            $this->model->removeOldMedia($this->model->$modelRelation);
        }
    }

    /**
     * Attach file to model
     *
     * @param             $fileId
     * @param Media|null  $currentModelValue
     * @param             $modelRelation
     * @param int         $order
     * @param bool        $hasMany - Determine that relation should be loaded inside or outside the function.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     */
    public function attachFile($fileId, ?Media $currentModelValue, $modelRelation, int $order = 1, $hasMany = false) : void
    {
        if ( $fileId ) {
            // Detect file.
            $file = $fileId instanceof Media ? $fileId : Media::findOrFail($fileId);

            if ( ! $currentModelValue || $file->getKey() != $currentModelValue->getKey() ) {

                // Move and save file in model.
                $path = $this->model->relativeMediaPath($file->name);

                if ( ! $file->is_raw ) {
                    MediaStorage::adapterByDisk($file->disk)->move(
                        $file->path,
                        $path,
                        config('filesystems.default') != $file->disk ?
                            config('filesystems.default') : NULL // Tell adapter that we want move through disks.
                    );
                }

                // Change file preferences after movement.
                $file->changeFilePreferences(
                    config('filesystems.default') != $file->disk ?
                        config('filesystems.default') : $file->disk,
                    $path
                );

                $this->model->saveMedia($file, $modelRelation, $order, $hasMany);

                // Remove old relation file references.
                if ( $currentModelValue ) {
                    $currentModelValue->model()->dissociate()->save();
                }
            }
        } elseif ( $modelRelation && $this->model->$modelRelation ) {
            $this->model->$modelRelation->delete();
            $this->model->unsetRelation($modelRelation);
        }
    }

}
