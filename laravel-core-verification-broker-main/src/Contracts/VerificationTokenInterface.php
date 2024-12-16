<?php

namespace AttractCores\LaravelCoreVerificationBroker\Contracts;

use \Illuminate\Support\Carbon;

/**
 * Interface VerificationTokenInterface
 *
 * @property string email - Email that requested a verification.
 * @property string verification_type - Verification type marker, that used to determine endpoints for workaround.
 * @property string web_token - Verification token, long.
 * @property string mobile_token - Verification token, shot. Configurable length.
 * @property Carbon created_at - Verification token created at date and time.
 *
 * @package AttractCores\LaravelCoreVerificationBroker\Contracts
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface VerificationTokenInterface
{
    /**
     * Fill the model with an array of attributes. Force mass assignment.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function forceFill(array $attributes);

    /**
     * Save the model to the database without raising any events.
     *
     * @param  array  $options
     * @return bool
     */
    public function saveQuietly(array $options = []);

    /**
     * Return tokens by email
     *
     * @param $query
     * @param $email
     */
    public function scopeByEmail($query, $email);

    /**
     * Return tokens by verification type
     *
     * @param $query
     * @param $vType
     */
    public function scopeByVType($query, string $vType);
}