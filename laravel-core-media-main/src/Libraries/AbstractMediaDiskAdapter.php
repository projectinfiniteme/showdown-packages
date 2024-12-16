<?php

namespace AttractCores\LaravelCoreMedia\Libraries;

use AttractCores\LaravelCoreMedia\Contracts\MediaDiskContract;
use AttractCores\LaravelCoreMedia\MediaStorage;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\Visibility;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class AbstractMediaDiskAdapter implements MediaDiskContract
{
    protected array $config;
    protected FilesystemOperator $storage;

    public function __construct(FilesystemOperator $storage, array $config)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    public function __call($method, $arguments)
    {
        return $this->storage->$method(...$arguments);
    }

    public function visibility(): bool
    {
        return $this->getConfigValue('visibility', Visibility::PRIVATE) === Visibility::PUBLIC;
    }

    public function temporaryUrl($path, $expiration, $options = []): string
    {
        try {
            return $this->storage->temporaryUrl($path, $expiration, $options);
        } catch (UnableToGenerateTemporaryUrl $e) {
            return $path;
        }
    }

    public function temporaryPath($path, $expiration, $options = []): string
    {
        $url = $this->temporaryUrl($path, $expiration, $options);

        if (Str::contains($url, '?')) {
            $parts = explode('?', $url);
            return sprintf('%s?%s', $path, $parts[1] ?? '');
        }

        return $path;
    }

    public function storage(): FilesystemOperator
    {
        return $this->storage;
    }

    public function delete(string $path): bool
    {
        try {
            $this->storage->delete($path);
            return true;
        } catch (UnableToDeleteFile $e) {
            return false;
        }
    }

    public function deleteDirectory(string $path): bool
    {
        try {
            $this->storage->deleteDirectory($path);
            return true;
        } catch (UnableToDeleteDirectory $e) {
            return false;
        }
    }

    public function move(string $pathFrom, string $pathTo, ?string $toDisk = null): bool
    {
        if (!$toDisk) {
            try {
                $this->storage->move($pathFrom, $pathTo);
                $this->storage->setVisibility($pathTo, $this->getConfigValue('visibility', Visibility::PRIVATE));
                return true;
            } catch (UnableToMoveFile $e) {
                return false;
            }
        } else {
            return $this->moveThroughDisks($toDisk, $pathFrom, $pathTo);
        }
    }

    protected function moveThroughDisks(string $toDisk, string $pathOnFromDisk, string $pathToOnCurrentDisk): bool
    {
        $toStorage = MediaStorage::adapterByDisk($toDisk);

        try {
            $mimeType = $this->storage->mimeType($pathOnFromDisk);
            
            if (str_starts_with($mimeType, 'image/') || app()->runningInConsole()) {
                $stream = $this->storage->readStream($pathOnFromDisk);
                $toStorage->writeStream($pathToOnCurrentDisk, $stream);
                $toStorage->setVisibility($pathToOnCurrentDisk, $this->getConfigValue('visibility', Visibility::PRIVATE));
                return true;
            } else {
                throw new \RuntimeException("Given file isn't an image or you are trying to move files cross disks in sync request.");
            }
        } catch (UnableToRetrieveMetadata $e) {
            throw new \RuntimeException("Given file doesn't exist or is not readable.");
        }
    }

    protected function getConfigValue($name, $default = null)
    {
        return Arr::get($this->config, $name, $default);
    }
}