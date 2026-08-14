<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;
use App\Helpers\JwtHelper;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthHeaders(User $user)
    {
        $token = JwtHelper::generateToken($user);
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_unauthenticated_user_cannot_access_report()
    {
        $response = $this->getJson('/api/report/monthly?merchant_id=1&month=2026-08');
        $response->assertStatus(401);
    }

    public function test_user_cannot_access_other_merchants_report()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $merchant = Merchant::create([
            'user_id' => $user1->id,
            'merchant_name' => 'Merchant 1',
            'created_by' => $user1->id,
            'updated_by' => $user1->id,
        ]);

        $headers = $this->getAuthHeaders($user2); // user2 trying to access user1's merchant

        $response = $this->getJson('/api/report/monthly?merchant_id=' . $merchant->id . '&month=2026-08', $headers);
        
        $response->assertStatus(403)
                 ->assertJson(['error' => 'Forbidden. Merchant does not belong to you or does not exist.']);
    }

    public function test_report_generates_correct_dates_including_zero_revenue_days()
    {
        $user = User::factory()->create();

        $merchant = Merchant::create([
            'user_id' => $user->id,
            'merchant_name' => 'Merchant 1',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $outlet = Outlet::create([
            'merchant_id' => $merchant->id,
            'outlet_name' => 'Outlet 1',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Insert some transactions
        DB::table('transactions')->insert([
            ['merchant_id' => $merchant->id, 'outlet_id' => $outlet->id, 'bill_total' => 1000, 'created_at' => '2026-08-01 10:00:00', 'created_by' => $user->id, 'updated_by' => $user->id],
            ['merchant_id' => $merchant->id, 'outlet_id' => $outlet->id, 'bill_total' => 2000, 'created_at' => '2026-08-01 12:00:00', 'created_by' => $user->id, 'updated_by' => $user->id],
            ['merchant_id' => $merchant->id, 'outlet_id' => $outlet->id, 'bill_total' => 1500, 'created_at' => '2026-08-15 12:00:00', 'created_by' => $user->id, 'updated_by' => $user->id],
        ]);

        $headers = $this->getAuthHeaders($user);

        // Fetch without pagination parameters to test default pagination (10 per page)
        $response = $this->getJson('/api/report/monthly?merchant_id=' . $merchant->id . '&month=2026-08', $headers);
        
        $response->assertStatus(200);
        $json = $response->json();

        // Check summary
        $this->assertEquals(4500, $json['summary']['total_revenue']);
        $this->assertEquals(3, $json['summary']['total_transactions']);

        // Check details pagination
        $this->assertArrayHasKey('details', $json);
        $this->assertArrayHasKey('data', $json['details']);
        $this->assertArrayHasKey('total', $json['details']);
        $this->assertEquals(31, $json['details']['total']); // August has 31 days

        // First day should have 3000 revenue
        $day1 = collect($json['details']['data'])->firstWhere('date', '2026-08-01');
        $this->assertEquals(3000, $day1['total_revenue']);
        $this->assertEquals(2, $day1['total_transactions']);

        // Second day should have 0 revenue (Testing the fill with 0 constraint)
        $day2 = collect($json['details']['data'])->firstWhere('date', '2026-08-02');
        $this->assertEquals(0, $day2['total_revenue']);
        $this->assertEquals(0, $day2['total_transactions']);
    }
}
