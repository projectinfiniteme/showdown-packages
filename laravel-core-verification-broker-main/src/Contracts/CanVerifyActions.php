<?php

namespace AttractCores\LaravelCoreVerificationBroker\Contracts;

/**
 * Interface CanVerifyActions
 *
 * @package AttractCores\LaravelCoreVerificationBroker\Contracts
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface CanVerifyActions
{

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForActionsVerification();
}