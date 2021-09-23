<?php namespace Codecycler\SURFconext\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Codecycler\SURFconext\Classes\TokenStorage;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::with('surfconext')->stateless()->redirect();
    }

    public function callback(Request $request, TokenStorage $storage)
    {
        $user = \Socialite::with('surfconext')->stateless()->user();

        /**
         * SURFconext does not support refresh tokens at this time. Just increase the access token lifetime if needed.
         */
        /*if (!$storage->saveRefresh($user['sub'], $user['iss'], $user->refreshToken)) {
            throw new TokenStorageException("Failed to save refresh token");
        }*/

        ray($user);

        /*// Authenticate the user
        $user = \RainLab\User\Models\User::findByEmail($user->getEmail());

        if (!$user) {
            $user = \RainLab\User\Models\User::create($data);
        }*/

        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'token' => $user->token,
        ];
    }

    public function refresh()
    {
    }
}
