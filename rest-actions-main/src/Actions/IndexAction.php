<?php

namespace Amondar\RestActions\Actions;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;

/**
 * Trait index action
 *
 * @version 1.0.0
 * @date    13.01.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait IndexAction
{

    /**
     * Return model instances.
     *
     * @param Router $router
     *
     * @extend getIndexFilterParameters - Declare this function to make request a little bit secure.
     * @extend extendIndexResponse - Declare this function to extend index response.
     *
     * @return mixed
     */
    public function index(Router $router)
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
        if ( $this->fireRestEvent('indexing') === false ) {
            return $this->restApiResponse(Response::HTTP_NO_CONTENT);
        }

        //Try to fetch data
        // Data fetching is needed if we want json response or
        // We need non simple view(view need db query data - from filter for example)
        if ( ! $this->isSimpleView() || $request->expectsJson() ) {
            $parameters = $this->callRestApiActionHook('getIndexFilterParameters', $request, $router);

            try {
                $data = $this->paginate($request, $parameters[ 'parameters' ]);
            } catch ( \Exception $e ) {
                $this->restApiActionsReportException($e);

                $data = collect([]);
            }
        } else {
            $data = collect([]);
        }

        //Fire index event.
        $this->fireRestEvent('index', $data, false);

        // Transform data if needed.
        // GetItems check if our current action want transformation.
        // If data is numeric, then we request count limit action.
        if ( $request->expectsJson() && ! is_numeric($data) ) {
            $data = $this->getItems($data);
        }

        // Prepare response.
        // Check if we want json.
        if ( $request->expectsJson() ) {
            return $this->restApiResponse(
                Response::HTTP_OK,
                is_numeric($data) ? [ 'count' => $data ] : $data, // If data is numeric, then we request count limit action.
                $this->callRestApiActionHook('extendIndexResponse', $request, $router)
            );
        } else {
            // Return view.
            return $this->render(! empty($this->action[ 'view' ]) ? $this->action[ 'view' ] : '')->with([ 'data' => $data ]);
        }
    }

}