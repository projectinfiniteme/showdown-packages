<?php

namespace AttractCores\LaravelCoreMedia\Jobs;

use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * class RemoveMediaModelPhysically
 *
 * @package AttractCores\LaravelCoreMedia\Jobs
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class RemoveMediaModelPhysically implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Media
     */
    protected Media $file;

    /**
     * Create a new job instance.
     *
     * @param Media $file
     */
    public function __construct(Media $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->file->removePhysically();
    }
}
