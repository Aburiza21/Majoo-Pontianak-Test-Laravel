<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\JwtHelper;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = JwtHelper::generateToken($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => 86400
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
