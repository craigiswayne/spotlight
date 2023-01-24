<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\RoleBase;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use \GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller
{
   /**
     * Redirect the user to the Okta authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider(\Illuminate\Http\Request $request)
    {
        if($request->has('url')) {
            $state = $request->url;
            return Socialite::driver('okta')->with(['state' => $state])->redirect();
        }

        return Socialite::driver('okta')->redirect();
    }

    /**
     * Obtain the user information from Okta.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(\Illuminate\Http\Request $request)
    {
        try
        {
            $user = Socialite::driver('okta')->stateless()->user();
        }
        catch(RequestException $ex)
        {
            // Invalid_grant
            if($ex->getResponse() != null && $ex->getResponse()->getStatusCode() == 400) {
                return redirect('/');
            }

            throw $ex;
        }

        $localUser = User::where('email', $user->email)->first();
        $normalSystemRole = RoleBase::where('typeid', '=', 1)->first();
        // create a local user with the email and token from Okta
        if (! $localUser) {
            $localUser = User::create([
                'email' => $user->email,
                'name'  => $user->name,
                'roleId' => $normalSystemRole->id,
                'token' => $user->token,
				'enabled' => 1
            ]);
        } else {
            // if the user already exists, just update the token:
            $localUser->token = $user->token;
            $localUser->save();
        }

		if ($localUser->enabled != 1) {
			return redirect('/');
		}

        try {
            Auth::login($localUser);
        } catch (\Throwable $e) {
            return redirect('/');
        }

        if ($request->has('state')) {
            $state  = base64_decode($request->state);
            if (Route::has($state))
            {
                return redirect($state);
            }
        }

        return redirect()->intended();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
