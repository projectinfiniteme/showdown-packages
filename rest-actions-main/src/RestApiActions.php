<?php

namespace Amondar\RestActions;

use Amondar\RestActions\Helpers\RestActionsAttributes;
use Amondar\RestActions\Helpers\RestActionsCacheHelper;
use Amondar\RestActions\Helpers\RestActionsCodeHooksHelper;
use Amondar\RestActions\Helpers\RestActionsEventsHelper;
use Amondar\RestActions\Helpers\RestActionsExtensionOnFlyHelper;
use Amondar\RestActions\Helpers\RestActionsFilterHelper;
use Amondar\RestActions\Helpers\RestActionsRepositoryHelper;
use Amondar\RestActions\Helpers\RestActionsSecurityHelper;
use Amondar\RestActions\Helpers\RestActionsTransformationHelper;

/**
 * Trait RestApiActions
 *
 * @version 1.0.0
 * @date    03.02.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestApiActions
{

    use RestActionsAttributes, RestActionsEventsHelper, RestActionsFilterHelper,
        RestActionsRepositoryHelper, RestActionsTransformationHelper, RestActionsExtensionOnFlyHelper,
        RestActionsSecurityHelper, RestActionsCodeHooksHelper, RestActionsCacheHelper;

    /**
     * Render view.
     *
     * @param $view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render($view)
    {
        if ( view()->exists($view) ) {
            return view($view, array_merge($this->viewData, $this->renderViewDefaultData()));
        }

        abort(404);
    }

    /**
     * TODO redeclare to add default data for all views.
     *
     * @return array
     */
    protected function renderViewDefaultData()
    {
        return [];
    }

    /**
     * Create rest api response.
     *
     * @require serverResponse function on coreController.
     *
     * @param      $status
     * @param      $data
     * @param      $extend
     *
     * @return mixed
     */
    protected function restApiResponse($status, $data = [], $extend = [])
    {
        $response = $this->serverResponse()
            ->status($status)
            ->resource($data)
            ->extend($extend);

        return $response;
    }

}