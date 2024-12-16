<?php

namespace Amondar\RestActions\Actions;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;

/**
 * Trait show action
 *
 * @version 1.0.0
 * @date    05.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait ShowAction
{

    /**
     * Return model instance.
     *
     * @param Router $router
     *
     * @extend getShowFilterParameters - Declare this function to make request a little bit secure.
     * @extend extendShowResponse - Declare this function to extend index response.
     *
     * @return mixed
     */
    public function show(Router $router)
    {
        // Check if we don't use action inline model declare.
        $this->checkActionInlineModelClass($router);

        // Run request validation.
        $request = $this->getValidationRequest($router);

        // Run security checks.
        $this->runQuerySecurityChecks($request);

        // If the "indexing" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel operation if validations fail or whatever.
        if ( $this->fireRestEvent('showing') === false ) {
            return $this->restApiResponse(Response::HTTP_NO_CONTENT);
        }

        //Try to fetch data
        // Data fetching is needed if we want json response or
        // We need non simple view(view need db query data - from filter for example)
        if ( ! $this->isSimpleView() || $request->expectsJson() ) {
            $parameters = $this->callRestApiActionHook('getShowFilterParameters', $request, $router);

            $data = $this->getItem(
                $this->getRestModelRouteValue($request),
                $request, $parameters[ 'parameters' ]
            );
        } else {
            $data = NULL;
        }

        //Fire show event.
        $this->fireRestEvent('show', $data, false);

        // Transform data if needed.
        // GetItems check if our current action want transformation.
        if ( $request->expectsJson() ) {
            $data = $this->getItems($data);
        }

        // Prepare response.
        // Check if we want json.
        if ( $request->expectsJson() ) {
            return $this->restApiResponse(
                Response::HTTP_OK,
                $data,
                $this->callRestApiActionHook('extendShowResponse', $request, $router)
            );
        } else {
            // Return view.
            return $this->render(! empty($action[ 'view' ]) ? $action[ 'view' ] : '')->with([ \Str::snake(class_basename($data)) => $data ]);
        }
    }

}