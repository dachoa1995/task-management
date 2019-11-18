<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use App\User;
use Illuminate\Support\Str;

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
        $userGoogle = Socialite::driver('google')->user();
        $user = User::where(['email' => $userGoogle->getEmail()])->first();
        if ($user) {
            return redirect('/home');
        } else {
            User::firstOrCreate([
                'email' => $userGoogle->getEmail(),
                'api_token' => Str::random(60),
                'name' => $userGoogle->getName(),
                'avatarURL' => $userGoogle->getAvatar(),
            ]);
            return redirect('/home');
        }
    }

}
