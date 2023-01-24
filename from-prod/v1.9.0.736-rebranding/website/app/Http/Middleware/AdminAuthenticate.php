<?php

namespace App\Http\Middleware;

use App\User;
use App\Http\Middleware\Authenticate;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class AdminAuthenticate extends Authenticate
{

    public function handle($request, Closure $next, ...$guards)
    {        
        parent::authenticate($request, $guards);
    
        if (auth()->user()->role->typeId >= 2) {
            return $next($request);
        }        

        return redirect('/');
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            $url = base64_encode($request->path());
            return route('login-okta', ['url' => $url]);
        }
    }
}
