<?php namespace Codecycler\SURFconext\Http\Controllers;

use Session;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use RainLab\User\Facades\Auth;
use System\Classes\PluginManager;
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
        $surfUser = Socialite::with('surfconext')->stateless()->user();

        // Authenticate the user
        $user = \RainLab\User\Models\User::findByEmail($surfUser->getEmail());

        if (!$user) {
            $password = Uuid::uuid4();

            $data = [
                'name' => $surfUser->name,
                'email' => $surfUser->email,
                'surname' => $surfUser->surname,
                'username' => $surfUser->email,
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $user = Auth::register($data, true);
        }

        // Create new token
        $user->createSurfConextToken($surfUser);

        // Attach to team if plugin is intalled
        if (PluginManager::instance()->exists('Codecycler.Teams')) {
            $team = \Codecycler\Teams\Models\Team::where('surfconext_organisation', $surfUser->organisation)
                ->first();

            if ($team && !$team->users->contains($user)) {
                $team->users()->add($user);
            }
        }


        Auth::loginUsingId($user->id);

        $request->session()->save();

        return redirect('/');
    }

    public function user()
    {
        $accessToken = Session::get('surfconext_access_token');

        //
        return Socialite::with('surfconext')->stateless()->userInfo($accessToken);
    }
}
