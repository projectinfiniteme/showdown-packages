<?php

namespace AttractCores\LaravelCoreAuth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Passport\Token;

class UserTokenIssued
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Laravel\Passport\Token
     */
    public Token $issuedToken;

    /**
     * Create a new event instance.
     *
     * @param \Laravel\Passport\Token $issuedToken
     */
    public function __construct(Token $issuedToken)
    {
        $this->issuedToken = $issuedToken;
    }

}
