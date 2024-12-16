<?php

namespace AttractCores\LaravelCoreMedia\Http\Controllers;


use Amondar\RestActions\Actions\StoreAction;
use AttractCores\LaravelCoreClasses\CoreController;
use AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use AttractCores\LaravelCoreMedia\Http\Requests\MediaRequest;
use AttractCores\LaravelCoreMedia\Http\Resources\MediaResource;
use AttractCores\LaravelCoreMedia\Repositories\MediaRepository;

/**
 * Class MediaController
 *
 * @version 1.0.0
 * @date    05/12/2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
class MediaController extends CoreController
{

    use StoreAction;

    /**
     * Possible rest actions.
     *
     * @var array
     */
    protected $actions = [
        'store' => [
            'request'     => MediaRequest::class,
            'transformer' => MediaResource::class,
            'onlyAjax'    => true,
        ],
    ];

    /**
     * FileController constructor.
     *
     * @param MediaRepository $repository
     */
    public function __construct(MediaRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Store S3 stored media.
     *
     * @param MediaRequest $request
     *
     * @return ServerResponse
     */
    public function storeS3(MediaRequest $request)
    {
        $media = $this->repository->storeS3($request);

        return $this->serverResponse()->resource(new MediaResource($media));
    }
}
