<?php

namespace AttractCores\LaravelCoreClasses;


use Amondar\RestActions\RestApiActions;
use AttractCores\LaravelCoreClasses\Libraries\ServerResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class CoreController
 *
 * @version 1.0.0
 * @date    03/12/2018
 * @author  Yure Nery <yurenery@gmail.com>
 */
class CoreController extends BaseController
{

    use RestApiActions, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return status code.
     *
     * @param int $code
     *
     * @return ServerResponse
     */
    public function serverResponse($code = 200)
    {
        return app('kit.response')
            ->status($code)
            ->extendWithToken()
            ->extendWithUser();
    }
}