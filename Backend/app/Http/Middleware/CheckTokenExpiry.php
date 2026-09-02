<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CheckTokenExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if ($token && str_contains($token, '|')) {
            [$id, $plainToken] = explode('|', $token, 2);
            $accessToken = PersonalAccessToken::find($id);

            if ($accessToken && hash_equals($accessToken->token, hash('sha256', $plainToken))) {
                if ($accessToken->expires_at && Carbon::now()->greaterThan($accessToken->expires_at)) {
                    $accessToken->delete();
                    return response()->json(['message' => 'Token has expired.'], 401);
                }
            }
        }

        return $next($request);
    }
}
