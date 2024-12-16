<?php

namespace AttractCores\LaravelCoreMedia;

use AttractCores\LaravelCoreMedia\Contracts\MediaDiskContract;
use AttractCores\LaravelCoreMedia\Libraries\AbstractMediaDiskAdapter;
use Illuminate\Support\Facades\Facade;

/**
 * Class MediaStorage
 *
 * @method static string getTmpFileName(string $fileName)
 * @method static AbstractMediaDiskAdapter adapter(string $diskName, array $config)
 * @method static AbstractMediaDiskAdapter adapterByDisk(string $diskName)
 * @method static string temporaryUrl(string $path, \DateTimeInterface $expiration, array $options = [])
 * @method static string temporaryPath(string $path, \DateTimeInterface $expiration, array $options = [])
 * @method static string temporaryUrlForUpload(string $path, \DateTimeInterface $expiration, array $options = [])
 * @method static \Illuminate\Contracts\Filesystem\Filesystem storage()
 *
 * @see \AttractCores\LaravelCoreMedia\Libraries\MediaStorage
 *
 * @package AttractCores\LaravelCoreMedia
 * Date: 21.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MediaStorage extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'kit-media.storage';
    }

}