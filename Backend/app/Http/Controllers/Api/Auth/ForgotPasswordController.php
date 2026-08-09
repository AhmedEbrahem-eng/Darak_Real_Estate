<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Mail;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        try
        {
            $user = User::where('email', $request->email)->first();

            // Generate a random token and send it within the frontend url
            $token = Str::random(60);
            $url = config('app.frontend_url') . '/reset-password?token=' . $token;

            $data['url'] = $url;
            $data['email'] = $request->email;
            $data['title'] = 'Password Reset Request';
            $data['user'] = $user;

            // Send the email
            Mail::send('forgetPasswordMail', ['data' => $data], function($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });

            // Save token in database
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            PasswordResetToken::updateOrCreate(
                ['email'=> $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at'=> $datetime,
                ]
            );

            return response()->json([
                'success' => true,
                'message'=> "Please check your email to reset your password",
            ], 200);
        }
        catch (\Throwable $th) 
        {
            Log::error($th->getMessage(), ['exception' => $th]);
            return response()->json([
                'success' => false,
                'message'=> 'An error occurred.',
            ], 500);
        }
    }
}
