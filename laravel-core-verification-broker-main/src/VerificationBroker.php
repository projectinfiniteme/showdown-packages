<?php

namespace AttractCores\LaravelCoreVerificationBroker;

use AttractCores\LaravelCoreVerificationBroker\Contracts\TokenRepositoryInterface;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use Closure;
use AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Arr;
use UnexpectedValueException;

/**
 * Class VerificationBroker
 *
 * @package ${NAMESPACE}
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerificationBroker implements VerificationBrokerContract
{

    /**
     * The password token repository.
     *
     * @var TokenRepositoryInterface
     */
    protected TokenRepositoryInterface $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected UserProvider $users;

    /**
     * VerificationBroker constructor.
     *
     * @param TokenRepositoryInterface                $tokens
     * @param \Illuminate\Contracts\Auth\UserProvider $users
     */
    public function __construct(TokenRepositoryInterface $tokens, UserProvider $users)
    {
        $this->tokens = $tokens;
        $this->users = $users;
    }

    /**
     * Send a verification action link to a user.
     *
     * @param array   $credentials
     * @param string  $tokenType
     * @param Closure $callback
     *
     * @return string
     */
    public function sendVerificationLink(array $credentials, string $tokenType, Closure $callback)
    {
        // Set broker type workflow.
        $this->tokens->onType($tokenType);

        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if ( is_null($user) ) {
            return static::INVALID_USER;
        }

        if ( $this->tokens->recentlyCreatedToken($user) ) {
            return static::RESET_THROTTLED;
        }

        $tokens = $this->tokens->create($user);

        // Run callback.
        $callback($user, $tokens);

        return $tokenType == 'passwords' ?
            static::PASSWORD_VERIFICATION_LINK_SENT :
            static::VERIFICATION_LINK_SENT;
    }

    /**
     * Verify given credentials
     *
     * @param array    $credentials
     * @param string   $tokenType
     * @param \Closure $callback
     *
     * @return \AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions|string|null
     */
    public function verify(array $credentials, string $tokenType, Closure $callback)
    {
        // Set tokens type workflow.
        $this->tokens->onType($tokenType);

        // Grab user and validate user and token.
        $user = $this->validateToken($credentials);

        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        if ( ! $user instanceof CanVerifyActions ) {
            return $user;
        }

        // Once the reset has been validated, we'll call the given callback with the
        // new password or user model only. This gives the user an opportunity to store the password
        // in their persistent storage. Then we'll delete the token and return.
        switch ( $tokenType ) {
            case 'passwords':
                $password = $credentials[ 'password' ];
                $callback($user, $password);
                break;
            default:
                $callback($user);
        }

        // Delete validated and processed token.
        $this->tokens->delete($user);

        return $tokenType == 'passwords' ? static::PASSWORD_RESET_VERIFICATION_PASSES : static::VERIFICATION_PASSES;
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param array $credentials
     *
     * @return CanVerifyActions|string
     */
    protected function validateToken(array $credentials)
    {
        if ( is_null($user = $this->getUser($credentials)) ) {
            return static::INVALID_USER;
        }

        if ( ! $this->tokens->exists($user, $credentials[ 'token' ]) ) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param array $credentials
     *
     * @return CanVerifyActions|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, [ 'token' ]);

        $user = $this->users->retrieveByCredentials($credentials);

        if ( $user && ! $user instanceof CanVerifyActions ) {
            throw new UnexpectedValueException('User model class must implement CanVerifyActions interface.');
        }

        return $user;
    }

    /**
     * Set short code format.
     *
     * @param $format
     *
     * @return $this
     */
    public function setShortCodeFormat($format) : self
    {
        $this->tokens->setShortCodeFormat($format);

        return $this;
    }

    /**
     * Set short code format.
     *
     * @param $length
     *
     * @return $this
     */
    public function setShortCodeLength($length) : self
    {
        $this->tokens->setShortCodeLength($length);

        return $this;
    }

}