<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Config;

class JwtHelper
{
    private static function getSecret()
    {
        return Config::get('app.key');
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public static function generateToken(User $user)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 hours
        ]);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decodeToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3) {
            return null;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        if (isset($payload['exp']) && time() > $payload['exp']) {
            return null;
        }

        return $payload;
    }
}
