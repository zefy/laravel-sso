<?php

namespace Zefy\LaravelSSO\Middleware;

use Closure;
use Zefy\LaravelSSO\LaravelSSOBroker;

class SSOAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $broker = new LaravelSSOBroker();

        $user = $broker->getUserInfo();

        if (isset($user['data'])) {
            if (auth()->guest() || auth()->user()->id != $user['data']['id']) {
                auth()->loginUsingId($user['data']['id']);
            }
        } elseif (!auth()->guest()) {
            auth()->logout();
        }

        return $next($request);
    }
}
