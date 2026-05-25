<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\RouteServiceProvider;

class GoogleController extends Controller
{
    private function isInAppBrowser(): bool
    {
        $ua = request()->header('User-Agent', '');
        $patterns = ['FBAN', 'FBAV', 'FB_IAB', 'Instagram', 'MicroMessenger',
                     'Musical', 'TikTok', 'Snapchat', 'Twitter', 'Line/'];
        foreach ($patterns as $p) {
            if (stripos($ua, $p) !== false) return true;
        }
        return false;
    }

    public function redirectToGoogle()
    {
        if ($this->isInAppBrowser()) {
            return view('auth.inapp-browser-warning');
        }

        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $verify = app()->isProduction() ? true : 'C:\php\cacert.pem';
        $guzzle = new Client(['verify' => $verify]);
        $googleUser = Socialite::driver('google')->setHttpClient($guzzle)->stateless()->user();

        // ვამოწმებთ, თუ მომხმარებელი უკვე არსებობს
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'nickname' => $googleUser->getName(),
                'password' => bcrypt(rand(1000,9999)) // დროებითი პაროლი
            ]
        );

        Auth::login($user, true);

        if ($user->level > 1) {
            return redirect()->route('levels.show', $user->level);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
