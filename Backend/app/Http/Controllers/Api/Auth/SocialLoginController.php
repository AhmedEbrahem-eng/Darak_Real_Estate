<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    private function handleCallback($provider)
    {
        try 
        {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->findOrCreateUser($socialUser, $provider);
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'social_type' => $provider,
                'user' => $user
            ]);
        } 
        catch (\Throwable $th) 
        {
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => "An internal server error occurred.",
            ], 500);
        }
    }

    private function findOrCreateUser($socialUser, $provider)
    {
        $user = User::where('social_id', $socialUser->id)->first();

        if ($user) {
            return $user;
        }

        return User::create([
            'name' => $socialUser->name,
            'email' => $socialUser->email,
            'social_id' => $socialUser->id,
            'social_type' => $provider,
            'role' => 'user',
            'password' => Hash::make('my-' . $provider),
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleGoogleCallback()
    {
        return $this->handleCallback('google');
    }

    public function handleFacebookCallback()
    {
        return $this->handleCallback('facebook');
    }
}
