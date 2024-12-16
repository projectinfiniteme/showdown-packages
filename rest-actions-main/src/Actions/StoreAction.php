<?php

namespace Amondar\RestActions\Actions;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Simplev\Libraries\ServerResponse;

/**
 * Trait StoreAction
 *
 * @version 1.0.0
 * @date    03.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait StoreAction
{

    /**
     * Store new instance to database.
     *
     * @param Router $router
     *
     * @extend extendStoreResponse - Declare this function to extend store response.
     *
     * @return mixed
     */
    public function store(Router $router)
    {
        // Check if we don't use action inline model declare.
        $this->checkActionInlineModelClass($router);

        // Run request validation.
        $request = $this->getValidationRequest($router);
        $responseStatus = Response::HTTP_CREATED;

        // If the "storing" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel operation if validations fail or whatever.
        if ( $this->fireRestEvent('storing') === false ) {
            $item = $this->restMakeModel();
            $responseStatus = Response::HTTP_NO_CONTENT;
        } else {
            // Determine repository using.
            if ( ! $this->canRunRepositoryAction($router) && $model = $this->restMakeModel() ) {
                $item = $model->create($this->getValidationRequestData($request));
            } else {
                $item = $this->runRepositoryAction($router, $request);
            }

            //Fire stored event.
            $this->fireRestEvent('stored', $item, false);
        }

        // Prepare response
        return $this->restApiResponse(
            $responseStatus,
            $this->getItems($item),
            $this->callRestApiActionHook('extendStoreResponse', $request, $router)
        );
    }

}