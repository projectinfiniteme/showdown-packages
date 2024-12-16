<?php
namespace AttractCores\LaravelCoreAuth\Http\Controllers;

use Exception;
use Laravel\Passport\Http\Controllers\AccessTokenController as PassportAccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response as Psr7Response;

/**
 * Class AccessTokenController
 * We need this controller reassign for OAUTH exceptions catch.
 * Just for pretty oauth exceptions appearance, cuz this exceptions generated out of laravel in oauth server code.
 *
 * @version 1.0.0
 * @date 12.10.17
 * @author Yure Nery <yurenery@gmail.com>
 */
class AccessTokenController extends PassportAccessTokenController
{

    /**
     * Authorize a client to access the user's account.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function issueToken(ServerRequestInterface $request)
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, new Psr7Response);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
