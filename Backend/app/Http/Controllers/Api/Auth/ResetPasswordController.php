<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function reset(ResetPasswordRequest $request)
    {
        try {
            // Find the password reset token
            $passwordResetToken = PasswordResetToken::where('token', $request->token)->first();
            
            if (!$passwordResetToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], 404);
            }

            // Find the user associated with this token
            $user = User::where('email', $passwordResetToken->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Update the user password
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the password reset token
            $passwordResetToken->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ], 200);
        } 
        catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "An error occurred while resetting the password",
            ], 500);
        }
    }
}
