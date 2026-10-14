<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    // 'facebook' temporarily disabled: requires SSM for client secret. Re-add once SSM is set up.
    protected array $providers = ['google'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        $socialUser = Socialite::driver($provider)->stateless()->user();
        $idColumn = "{$provider}_id";

        $user = User::where($idColumn, $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if ($user) {
            if (! $user->{$idColumn}) {
                $user->forceFill([$idColumn => $socialUser->getId()])->save();
            }
        } else {
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                $idColumn => $socialUser->getId(),
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
