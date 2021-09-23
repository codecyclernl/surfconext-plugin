<?php namespace Codecycler\SURFconext\Http\Controllers;

use Session;
use Illuminate\Http\Request;
use RainLab\User\Facades\Auth;
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
        $surfUser = \Socialite::with('surfconext')->stateless()->user();

        // Authenticate the user
        $user = \RainLab\User\Models\User::findByEmail($surfUser->getEmail());

        if (!$user) {
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'surname' => $user->surname,
                'username' => $user->email,
            ];

            $user = \RainLab\User\Models\User::create($data);
        }

        // Create new token
        $user->createSurfConextToken($surfUser);

        Auth::loginUsingId($user->id);

        $request->session()->save();

        return redirect('https://surfconext.share.codecycler.dev/surfconext/user/test');
    }

    public function user()
    {
        $accessToken = Session::get('surfconext_access_token');

        //
        ray(\Socialite::with('surfconext')->stateless()->userInfo($accessToken));
    }
}
