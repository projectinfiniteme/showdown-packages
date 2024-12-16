<?php

namespace AttractCores\LaravelCoreMedia\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use \AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Queue\SerializesModels;

/**
 * Class MediaDeleted
 *
 * @package AttractCores\LaravelCoreMedia\Events
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MediaDeleted
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Media
     */
    public Media $file;

    /**
     * MediaRemoved constructor.
     *
     * @param Media $file
     */
    public function __construct(Media $file)
    {
        $this->file = $file;
    }

}