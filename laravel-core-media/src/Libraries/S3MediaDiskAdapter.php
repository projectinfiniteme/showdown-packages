<?php

namespace AttractCores\LaravelCoreMedia\Libraries;

/**
 * Class S3MediaDriver
 *
 * @package AttractCores\LaravelCoreMedia\Libraries
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class S3MediaDiskAdapter extends AbstractMediaDiskAdapter
{

    /**
     * Return pre-signed url.
     *
     * @param       $path
     * @param       $expiration
     * @param array $options
     *
     * @return string
     */
    public function temporaryUrlForUpload($path, $expiration, $options = []) : string
    {
        $s3Adapter = $this->storage->getAdapter();

        /** @var \Aws\S3\S3Client $s3Client */
        $s3Client = $s3Adapter->getClient();

        $cmd = $s3Client->getCommand('PutObject', array_merge([
            'Bucket' => $s3Adapter->getBucket(),
            'Key'    => $path,
        ], $options));

        return (string) $s3Client->createPresignedRequest($cmd, $expiration)->getUri();
    }

    /**
     * Return bucket name.
     *
     * @return string
     */
    public function getBucket() : string
    {
        return $this->storage->getAdapter()->getBucket();
    }

}