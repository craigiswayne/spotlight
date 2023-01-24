<?php

namespace App\Http\Middleware;

use App\User;
use App\Http\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Helpers\SecureHelper;
use Closure;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class PermissionNormal extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {                
        if(!SecureHelper::hasNormalAccess($guards)) {
            abort(404);
        }
        
        return $next($request);
    }
}
