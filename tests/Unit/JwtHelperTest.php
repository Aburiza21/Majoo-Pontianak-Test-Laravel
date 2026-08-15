<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\JwtHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_token_returns_valid_format()
    {
        $user = User::factory()->create();
        $token = JwtHelper::generateToken($user);

        $this->assertIsString($token);
        
        // JWT tokens usually have 3 parts separated by dots
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }
}
