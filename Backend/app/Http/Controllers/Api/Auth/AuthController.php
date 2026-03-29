<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        try 
        {
            $role = in_array($request->role, ['user', 'landlord']) ? $request->role : 'user';

            User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $role,
                'phone_number' => '+2' . $request->phone_number,
                'avatar' => 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Default_pfp.svg'
            ]);

            return response()->json([
                'success' => true,
                'message'=> "User registered successfully",
            ], 201);
        } 
        catch (\Throwable $th) 
        {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try
        {
            $user = User::where('email', $request->email)->first();
    
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message'=> "Email or password is incorrect",
                ], 401);
            }

            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            // Set token expiration date to 24 hours
            $expiration = Carbon::now()->addDay();
            $tokenResult->accessToken->expires_at = $expiration;
            $tokenResult->accessToken->save();
    
            return response()->json([
                'success'=> true,
                'message'=> 'User logged in successfully',
                'user'=> new UserResource($user),
                'access_token' => $token,
            ]);
        }
        catch (\Throwable $th) 
        {
            return response()->json([
                'success' => false,
                'message'=> $th->getMessage(),
            ], 500);
        }
    }

    public function profile(Request $request) 
    {
        try 
        {
            $userData = auth()->user();
            return response()->json([
                'success'=> true,
                'message'=> 'Profile Information',
                'user' => new UserResource($userData),
            ], 200);
        }
        catch (\Throwable $th) 
        {
            return response()->json([
                'success' => false,
                'message'=> $th->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request) 
    {
        try 
        {
            auth()->user()->tokens()->delete();
            return response()->json([
                'success'=> true,
                'message'=> 'User logged out successfully',
                'data' => [],
            ], 200);
        }
        catch (\Throwable $th) 
        {
            return response()->json([
                'success' => false,
                'message'=> $th->getMessage(),
            ], 500);
        }
    }
}
