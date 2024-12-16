<?php

namespace AttractCores\LaravelCoreAuth\Http\Middleware;

use Closure;

class SetUserExtensions
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @param string                   $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'api')
    {
        $user = $request->user($guard);

        auth()->shouldUse($guard);
        
        if ($user) {
            $user = $user->load([ 'permissions' ]);

            auth()->setUser($user);
        }

        return $next($request);
    }
}
