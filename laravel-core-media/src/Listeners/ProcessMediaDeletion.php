<?php

namespace AttractCores\LaravelCoreMedia\Listeners;

use AttractCores\LaravelCoreAuth\Events\Registered;
use AttractCores\LaravelCoreMedia\Events\MediaDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class ProcessMediaDeletion
 *
 * @package AttractCores\LaravelCoreMedia
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ProcessMediaDeletion implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param \AttractCores\LaravelCoreMedia\Events\MediaDeleted $event
     *
     * @return void
     */
    public function handle(MediaDeleted $event)
    {
        $event->file->removePhysically();
    }

}