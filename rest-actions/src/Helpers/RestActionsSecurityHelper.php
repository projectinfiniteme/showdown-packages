<?php

namespace Amondar\RestActions\Helpers;

use Illuminate\Http\Request;

/**
 * Trait RestActionsSecurityHelper
 *
 * @version 1.0.0
 * @date    01.08.17
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait RestActionsSecurityHelper
{

    /**
     * Check if we need only view part of route.
     *
     * @return bool
     */
    public function isSimpleView()
    {
        $action = $this->currentAction();

        return $action ? $action[ 'simpleView' ] : false;
    }

    /**
     * Check if action need transformation.
     *
     * @return bool
     */
    public function needTransformation()
    {
        $action = $this->currentAction();

        return $action ? $action[ 'ajaxTransform' ] !== false : true;
    }


    /**
     * Run query security checks.
     *
     * @param Request $request
     */
    protected function runQuerySecurityChecks(Request $request)
    {
        if ( $this->action[ 'onlyAjax' ] && ! $request->expectsJson() ) {
            abort(404, 'This route receive only ajax calls. Try with ajax header.');
        } elseif ( $this->action[ 'onlyBrowser' ] && $request->expectsJson() ) {
            abort(404, 'This route receive only browser calls. Try from it.');
        }
    }

}