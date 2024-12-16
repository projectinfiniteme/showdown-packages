<?php

namespace AttractCores\LaravelCoreAuth\Events;

use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class Registered
 *
 * @package AttractCores\LaravelCoreAuth\Events
 * Date: 15.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class Registered
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public Authenticatable $user;

    /**
     * Request side for password reset url generation.
     *
     * @var string
     */
    public string $requestSide;

    /**
     * Registered constructor.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $requestSide
     */
    public function __construct(Authenticatable $user, $requestSide = CoreUserContract::FRONTEND_REQUEST_SIDE)
    {

        $this->user = $user;
        $this->requestSide = $requestSide;
    }

}