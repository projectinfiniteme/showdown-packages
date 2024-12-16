<?php

namespace AttractCores\LaravelCoreMedia\Http\Controllers;

use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use AttractCores\LaravelCoreMedia\Http\Requests\PreSignedRequest;
use AttractCores\LaravelCoreMedia\MediaStorage;

/**
 * Class S3PreSignController
 *
 * @version 1.0.0
 * @date    06.07.2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
class S3PreSignController extends CoreController
{

    /**
     * Generate pre digned url for files into source bucket.
     *
     * @param PreSignedRequest $request
     *
     * @return ServerResponse
     */
    public function handleForSources(PreSignedRequest $request)
    {
        $preSignedLifeTime = config('kit-media.pre_signed_lifetime.upload');
        $validated = $request->validated();

        return $this->serverResponse()->data([
            'pre-signed-url' => MediaStorage::adapterByDisk('s3')
                                            ->temporaryUrlForUpload(
                                                $validated[ 'Key' ],
                                                "+$preSignedLifeTime minutes", // 6 hours for upload
                                                $validated
                                            ),
        ]);
    }

    /**
     * Return link to file into source bucket.
     *
     * @param PreSignedRequest $request
     *
     * @return ServerResponse
     */
    public function getSourceLink(PreSignedRequest $request)
    {
        $s3Path = $request->input('s3_path');
        $preSignedLifeTime = config('kit-media.pre_signed_lifetime.sources');

        return $this->serverResponse()->data([
            'link' => MediaStorage::adapterByDisk('s3')->temporaryUrl($s3Path, "+$preSignedLifeTime minutes"),
        ]);
    }

}