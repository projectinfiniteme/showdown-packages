<?php

namespace AttractCores\LaravelCoreVerificationBroker\Repositories;

use AttractCores\LaravelCoreVerificationBroker\Contracts\CanVerifyActions;
use AttractCores\LaravelCoreVerificationBroker\Contracts\TokenRepositoryInterface;
use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationTokenInterface;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ShortCode\Random as ShortCode;

/**
 * Class TokenRepository
 *
 * @package ${NAMESPACE}
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class TokenRepository implements TokenRepositoryInterface
{

    /**
     * The model to interact with DB.
     *
     * @var VerificationTokenInterface
     */
    protected VerificationTokenInterface $verificationTokenModel;

    /**
     * The Hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected Hasher $hasher;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected string $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected int $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     *
     * @var int
     */
    protected int $throttle;

    /**
     * Repository configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Determine the type of verification processes.
     *
     * @var string
     */
    protected string $type = 'passwords';

    /**
     * Short code format.
     *
     * @var string
     */
    protected string $shortCodeFormat;

    /**
     * Short code length.
     *
     * @var int
     */
    protected int $shortCodeLength;


    /**
     * Create a new token repository instance.
     *
     * @param VerificationTokenInterface           $verificationTokenModel
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param string                               $hashKey
     * @param array                                $config
     */
    public function __construct(VerificationTokenInterface $verificationTokenModel, HasherContract $hasher,
        string $hashKey, array $config)
    {
        $this->hasher = $hasher;
        $this->hashKey = $hashKey;
        $this->expires = $config[ 'lifetime' ][ 'expires' ] * 60;
        $this->throttle = $config[ 'lifetime' ][ 'throttle' ];
        $this->verificationTokenModel = $verificationTokenModel;
        $this->config = $config;
        $this->shortCodeLength = $config[ 'length' ][ 'mobile' ];
        $this->shortCodeFormat = $config[ 'mobile_format' ];
    }

    /**
     * Set working on given type
     *
     * @param string $type
     *
     * @return $this
     */
    public function onType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Create a new token.
     *
     * @param CanVerifyActions $user
     *
     * @return array
     */
    public function create(CanVerifyActions $user)
    {
        $email = $user->getEmailForActionsVerification();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $webToken = $this->createNewToken();
        $mobileToken = $this->createNewToken(true);

        $this->verificationTokenModel->forceFill($this->getPayload($email, $webToken, $mobileToken))
                                     ->saveQuietly();

        return [
            'mobile' => $mobileToken,
            'web'    => $webToken,
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param CanVerifyActions $user
     * @param string           $token
     *
     * @return bool
     */
    public function exists(CanVerifyActions $user, $token)
    {
        /** @var VerificationTokenInterface $record */
        $record = $this->verificationTokenModel->byVType($this->type)
                                               ->byEmail($user->getEmailForActionsVerification())
                                               ->first();

        return $record &&
               ! $this->tokenExpired($record->created_at) && (
                   $this->hasher->check($token, $record->web_token) ||
                   $this->hasher->check($token, $record->mobile_token)
               );
    }

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param CanVerifyActions $user
     *
     * @return bool
     */
    public function recentlyCreatedToken(CanVerifyActions $user)
    {
        $record = $this->verificationTokenModel->byVType($this->type)
                                               ->byEmail($user->getEmailForActionsVerification())
                                               ->first();

        return $record && $this->tokenRecentlyCreated($record[ 'created_at' ]);
    }

    /**
     * Determine if the token was recently created.
     *
     * @param Carbon $createdAt
     *
     * @return bool
     */
    protected function tokenRecentlyCreated(Carbon $createdAt)
    {
        if ( $this->throttle <= 0 ) {
            return false;
        }

        return $createdAt->addSeconds(
            $this->throttle
        )->isFuture();
    }

    /**
     * Delete a token record by user.
     *
     * @param CanVerifyActions $user
     *
     * @return void
     */
    public function delete(CanVerifyActions $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = now()->subSeconds($this->expires);

        $this->verificationTokenModel->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Determine if the token has expired.
     *
     * @param Carbon $createdAt
     *
     * @return bool
     */
    protected function tokenExpired(Carbon $createdAt)
    {
        return $createdAt->addSeconds($this->expires)->isPast();
    }

    /**
     * Build the record payload for the table.
     *
     * @param string $email
     * @param string $webToken
     * @param string $mobileToken
     *
     * @return array
     */
    protected function getPayload($email, string $webToken, string $mobileToken)
    {
        return [
            'email'             => $email,
            'verification_type' => $this->type,
            'web_token'         => $this->hasher->make($webToken),
            'mobile_token'      => $this->hasher->make($mobileToken),
            'created_at'        => now(),
        ];
    }

    /**
     * Create a new token for the user.
     *
     * @param bool $short
     *
     * @return string
     */
    public function createNewToken($short = false)
    {
        return ! $short ? hash_hmac('sha256', Str::random($this->config[ 'length' ][ 'web' ]), $this->hashKey) :
            ShortCode::get($this->shortCodeLength, $this->shortCodeFormat);
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
        $this->shortCodeFormat = $format;

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
        $this->shortCodeLength = $length < 6 ? 6 : ( $length > 20 ? 20 : $length );

        return $this;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param CanVerifyActions $user
     *
     * @return int
     */
    protected function deleteExisting(CanVerifyActions $user)
    {
        return $this->verificationTokenModel->byVType($this->type)
                                            ->byEmail($user->getEmailForActionsVerification())
                                            ->delete();
    }

}