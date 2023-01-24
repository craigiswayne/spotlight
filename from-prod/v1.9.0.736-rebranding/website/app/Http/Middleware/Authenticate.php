<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        // When accessing these assets, user should already be logged in, so we not redirecting.
        // This will make it appear that the files doesn't exist to web crawlers
        if($request->is('assets/storage/*')) {
            return abort(404);
        }

        if (! $request->expectsJson()) {
            $url = base64_encode($request->path());
            return route('login-okta', ['url' => $url]);
        }
    }
}
