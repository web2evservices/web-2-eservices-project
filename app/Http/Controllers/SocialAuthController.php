<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected array $providers = ['google', 'github', 'linkedin-openid', 'facebook'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['login' => 'OAuth failed. Please try again.']);
        }

        // Find or create the user
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'username'          => $socialUser->getNickname()
                                       ?? $socialUser->getName()
                                       ?? 'user_' . uniqid(),
                'password'          => bcrypt(\Illuminate\Support\Str::random(24)),
                'tel'               => '', // OAuth providers don't supply phone numbers
                'oauth_provider'    => $provider,
                'oauth_id'          => $socialUser->getId(),
            ]
        );

        Auth::login($user, remember: true);

        return view('users.dashboard');
    }
}