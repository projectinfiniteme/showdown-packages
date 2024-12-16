<?php

namespace AttractCores\LaravelCoreVerificationBroker\Contracts;

/**
 * Interface TokenRepositoryInterface
 *
 * @package ${NAMESPACE}
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface TokenRepositoryInterface
{

    /**
     * Create a new token.
     *
     * @param CanVerifyActions $user
     *
     * @return array
     */
    public function create(CanVerifyActions $user);

    /**
     * Set short code format.
     *
     * @param $format
     *
     * @return $this
     */
    public function setShortCodeFormat($format): self;

    /**
     * Set short code format.
     *
     * @param $length
     *
     * @return $this
     */
    public function setShortCodeLength($length): self;

    /**
     * Set working on given type
     *
     * @param string $type
     *
     * @return $this
     */
    public function onType(string $type);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  CanVerifyActions  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanVerifyActions $user, $token);

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param  CanVerifyActions  $user
     * @return bool
     */
    public function recentlyCreatedToken(CanVerifyActions $user);

    /**
     * Delete a token record.
     *
     * @param  CanVerifyActions  $user
     * @return void
     */
    public function delete(CanVerifyActions $user);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();
}