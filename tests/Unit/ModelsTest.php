<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_merchants()
    {
        $user = User::factory()->create();
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'merchant_name' => 'Test Merchant',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertTrue($user->merchants->contains($merchant));
        $this->assertEquals(1, $user->merchants->count());
        $this->assertInstanceOf(Merchant::class, $user->merchants->first());
    }

    public function test_merchant_belongs_to_user()
    {
        $user = User::factory()->create();
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'merchant_name' => 'Test Merchant',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertEquals($user->id, $merchant->user->id);
        $this->assertInstanceOf(User::class, $merchant->user);
    }

    public function test_merchant_has_many_outlets()
    {
        $user = User::factory()->create();
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'merchant_name' => 'Test Merchant',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $outlet = Outlet::create([
            'merchant_id' => $merchant->id,
            'outlet_name' => 'Test Outlet',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertTrue($merchant->outlets->contains($outlet));
        $this->assertEquals(1, $merchant->outlets->count());
        $this->assertInstanceOf(Outlet::class, $merchant->outlets->first());
    }
}
