<?php

namespace AttractCores\LaravelCoreMedia\Commands;

use AttractCores\LaravelCoreMedia\Jobs\RemoveMediaModelPhysically;
use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class PruneOldFiles
 *
 * @package AttractCores\LaravelCoreMedia
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class PruneOldMedia extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kit:media:prune-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command prune existing old files, that are not connected to any data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $cursor = Media::withoutGlobalScopes()
                       ->where('created_at', '<', now()->subMinutes(config('kit-media.delete_retention')))
                       ->where(function ($query) {
                           $query->orWhereNull('model_id')
                                 ->orWhereNull('model_type')
                                 ->orWhereNotNull('deleted_at')
                                 ->orWhereDoesntHave('model');
                       })->cursor();

        foreach ( $cursor as $file ) {
            RemoveMediaModelPhysically::dispatch($file)
                                      ->onQueue(config('kit-media.default_queue'))
                                      ->delay(now()->addSeconds(5));
        }

        $this->info('Done!!');
    }

}
