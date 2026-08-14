<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Helpers\JwtHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);
        $payload = JwtHelper::decodeToken($token);

        if (!$payload) {
            return response()->json(['error' => 'Token invalid or expired'], 401);
        }

        $user = User::find($payload['user_id']);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }

        // Bind authenticated user to request
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
