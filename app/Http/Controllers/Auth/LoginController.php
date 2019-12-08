<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @return mixed
     */
    public function handleProviderCallback()
    {
        $userGoogle = Socialite::driver('google')->stateless()->user();
        $user = User::where(['email' => $userGoogle->getEmail()])->first();
        //Generate random unique token
        $api_token = Str::random(50) . date('YmdHis');
        if (!$user) {
            $user = User::firstOrCreate([
                'email' => $userGoogle->getEmail(),
                'api_token' => $api_token,
                'name' => $userGoogle->getName(),
                'avatarURL' => $userGoogle->getAvatar(),
            ]);
        } elseif(is_null($user['api_token'])) {
            $user->api_token = $api_token;
            $user->name = $userGoogle->getName();
            $user->avatarURL = $userGoogle->getAvatar();
            $user->save();
        }
        Auth::login($user);
        return view('callback', [
            'name' => $user->name,
            'api_token' => $user->api_token,
        ]);
    }

    public function logout() {
        Auth::logout();
        return response()->json([], 204);
    }

}
