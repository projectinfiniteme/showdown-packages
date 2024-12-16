<?php

namespace Amondar\RestActions\Actions;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Simplev\Libraries\ServerResponse;

/**
 * Trait destroy action
 *
 * @version 1.0.0
 * @date    13.01.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait DestroyAction
{

    /**
     * Destroy model instances.
     *
     * @param Router $router
     *
     * @extend extendDestroyResponse - Declare this function to extend destroy response.
     *
     * @return mixed
     */
    public function destroy(Router $router)
    {
        // Check if we don't use action inline model declare.
        $this->checkActionInlineModelClass($router);

        // Run request validation.
        $request = $this->getValidationRequest($router);

        $responseStatus = Response::HTTP_OK;

        if ($this->fireRestEvent('deleting') === false) {
            $responseStatus = Response::HTTP_NO_CONTENT;
        }else {
            // Run destroy action.
            $item = $this->getModelInstanceFromRoute($request, $this->getRestModelRouteKeyName());
            if ( $this->canRunRepositoryAction($router) ) {
                $this->runRepositoryAction($router, $request);
            } else {
                $item->delete();
            }

            //Fire deleted event.
            $this->fireRestEvent('deleted', $item, false);
        }

        // Make a request.
        return $this->restApiResponse(
            $responseStatus,
            [ ],
            $this->callRestApiActionHook('extendDestroyResponse', $request, $router)
        );
    }

}