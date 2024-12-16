<?php

namespace AttractCores\LaravelCoreVerificationBroker\Contracts;

use Closure;

/**
 * Interface VerificationBrokerContract
 *
 * @package AttractCores\LaravelCoreVerificationBroker\Contracts
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
interface VerificationBrokerContract
{
    public const INVALID_USER                       = "We can't find a user with that e-mail address.";

    public const RESET_THROTTLED                    = "Please, wait before retrying.";

    public const VERIFICATION_LINK_SENT             = 'We have e-mailed your verification link!';

    public const PASSWORD_VERIFICATION_LINK_SENT    = 'We have e-mailed your password reset link!';

    public const PASSWORD_RESET_VERIFICATION_PASSES = 'Your password has been reset!';

    public const VERIFICATION_PASSES                = 'Account action verified!';

    public const INVALID_TOKEN                      = 'This verification token is invalid.';

    /**
     * Send a verification action link to a user.
     *
     * @param array   $credentials
     * @param string  $tokenType
     * @param Closure $callback
     *
     * @return string
     */
    public function sendVerificationLink(array $credentials, string $tokenType, Closure $callback);

    /**
     * Verify given credentials
     *
     * @param array    $credentials
     * @param string   $tokenType
     * @param \Closure $callback
     *
     * @return \AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions|string|null
     */
    public function verify(array $credentials, string $tokenType, Closure $callback);

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
}