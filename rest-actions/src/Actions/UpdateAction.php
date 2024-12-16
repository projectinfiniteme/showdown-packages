<?php

namespace Amondar\RestActions\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;

/**
 * Trait UpdateAction
 *
 * @version 1.0.0
 * @date    03.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait UpdateAction
{

    /**
     * Update exists instance in database.
     *
     * @param Router $router
     *
     * @extend extendUpdateResponse - Declare this function to extend update response.
     *
     * @return mixed
     */
    public function update(Router $router)
    {
        // Check if we don't use action inline model declare.
        $this->checkActionInlineModelClass($router);

        // Run request validation.
        $request = $this->getValidationRequest($router);
        $responseStatus = Response::HTTP_OK;

        // If the "updating" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel operation if validations fail or whatever.
        if ( $this->fireRestEvent('updating') === false ) {
            $item = $this->restMakeModel();
            $responseStatus = Response::HTTP_NO_CONTENT;
        } else {
            $item = $this->getModelInstanceFromRoute($request, $this->getRestModelRouteKeyName());

            if ( ! $this->canRunRepositoryAction($router) && $item ) {
                $item->update($this->getValidationRequestData($request));
            } else {
                $item = $this->runRepositoryAction($router, $request);
            }

            //Fire updated event.
            $this->fireRestEvent('updated', $item, false);
        }

        return $this->restApiResponse(
            $responseStatus,
            $this->getItems($item),
            $this->callRestApiActionHook('extendUpdateResponse', $request, $router)
        );
    }

    /**
     * Return model attributes, if route model in route is empty.
     *
     * @note User this function for example for profile updating.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function getUpdateActionAttributesOnEmptyRoute(Request $request)
    {
        return [];
    }

}
