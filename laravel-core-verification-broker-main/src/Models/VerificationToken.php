<?php

namespace AttractCores\LaravelCoreVerificationBroker\Models;

use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationTokenInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VerificationToken
 *
 * @package ${NAMESPACE}
 * Date: 12.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerificationToken extends Model implements VerificationTokenInterface
{
    /**
     * Tell model class ignore timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Possible date fields.
     *
     * @var string[]
     */
    protected $dates = [ 'created_at' ];

    /**
     * Return tokens by email
     *
     * @param $query
     * @param $email
     */
    public function scopeByEmail($query, $email)
    {
        $query->where('email', $email);
    }

    /**
     * Return tokens by verification type
     *
     * @param $query
     * @param $vType
     */
    public function scopeByVType($query, string $vType)
    {
        $query->where('verification_type', $vType);
    }

}