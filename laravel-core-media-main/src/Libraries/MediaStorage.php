<?php

namespace AttractCores\LaravelCoreMedia\Libraries;

use League\Flysystem\FilesystemOperator;
use AttractCores\LaravelCoreMedia\Libraries\S3MediaDiskAdapter;
use AttractCores\LaravelCoreMedia\Libraries\LocalMediaDiskAdapter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Class MediaBroker
 *
 * @package AttractCores\LaravelCoreMedia
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class MediaStorage
{

    protected array $adapters = [];

    protected array $config = [];

    /**
     * MediaBroker constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Handle dynamic method calls into the adapter storage adapter.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if ( $method == 'adapter' || $method == 'adapterByDisk' ) {
            return $this->$method(...$parameters);
        }

        return $this->adapterByDisk(config('filesystems.default'))->$method(...$parameters);
    }


    /**
     * Set the adapter to work with.
     *
     * @param string $diskName
     * @param array  $config
     *
     * @return AbstractMediaDiskAdapter
     */
    public function adapter(string $diskName, array $config)
    {
        if ( ! empty($this->adapters[ $diskName ]) ) {
            return $this->adapters[ $diskName ];
        }

        $adapterMethod = 'create' . ucfirst($config[ 'driver' ]) . 'Adapter';

        if ( method_exists($this, $adapterMethod) ) {
            return $this->adapters[ $diskName ] = $this->{$adapterMethod}(Storage::disk($diskName), $config);
        } else {
            throw new InvalidArgumentException("Media adapter [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Return adapter by filesystem disk name.
     *
     * @param $diskName
     *
     * @return AbstractMediaDiskAdapter
     */
    public function adapterByDisk($diskName)
    {
        $diskFilesystemConfig = config("filesystems.disks.$diskName", NULL);

        if ( ! $diskFilesystemConfig ) {
            throw new InvalidArgumentException("Filesystem disk [{$diskName}] does not exists.");
        }

        return $this->adapter($diskName, $diskFilesystemConfig);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function getTmpFileName(string $fileName)
    {
        return $this->config['tmp_path'] . $fileName;
    }

    /**
     * Create s3 adapter.
     *
     * @param FilesystemOperator $storage
     * @param array $config
     *
     * @return S3MediaDiskAdapter
     */
    protected function createS3Adapter(FilesystemOperator $storage, array $config): S3MediaDiskAdapter
    {
        return new S3MediaDiskAdapter($storage, $config);
    }

    /**
     * Create local adapter.
     *
     * @param FilesystemOperator $storage
     * @param array $config
     *
     * @return LocalMediaDiskAdapter
     */
    protected function createLocalAdapter(FilesystemOperator $storage, array $config): LocalMediaDiskAdapter
    {
        return new LocalMediaDiskAdapter($storage, $config);
    }

}