<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\RouteServiceProvider;

class GoogleController extends Controller
{
    private function isInAppBrowser(): bool
    {
        $ua = request()->header('User-Agent', '');
        foreach (['FBAN', 'FBAV', 'FB_IAB', 'Instagram', 'MicroMessenger',
                  'Musical', 'TikTok', 'Snapchat', 'Twitter', 'Line/'] as $p) {
            if (stripos($ua, $p) !== false) return true;
        }
        return false;
    }

    private function generateParentCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (User::where('parent_code', $code)->exists());

        return $code;
    }

    public function redirectToGoogle()
    {
        if ($this->isInAppBrowser()) {
            $copyUrl = session('url.intended', config('app.url'));
            return view('auth.inapp-browser-warning', compact('copyUrl'));
        }

        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $verify = app()->isProduction() ? true : 'C:\php\cacert.pem';
        $guzzle = new Client(['verify' => $verify]);
        $googleUser = Socialite::driver('google')->setHttpClient($guzzle)->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'      => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'password'  => bcrypt(rand(1000, 9999)),
            ]
        );

        // role: admin ინახება, დანარჩენი → parent; parent_code ყველას ეძლევა
        $data = [];
        if ($user->role !== 'admin') {
            $data['role'] = 'parent';
        }
        if (! $user->parent_code) {
            $data['parent_code'] = $this->generateParentCode();
        }
        if ($data) {
            $user->update($data);
        }

        Auth::login($user, true);

        if (session()->has('url.intended')) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return redirect()->route('dashboard');
    }
}
