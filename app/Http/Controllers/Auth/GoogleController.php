<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\RouteServiceProvider;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

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

        Auth::login($user, true); // ავტორიზაცია

        return redirect()->intended(RouteServiceProvider::HOME); // Breeze-ის default home
    }
}
