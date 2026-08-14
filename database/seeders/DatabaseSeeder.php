<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        User::create([
            'id' => 1,
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password')
        ]);
        
        User::create([
            'id' => 2,
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password')
        ]);

        // Seed merchants
        DB::insert("insert into merchants (id, user_id, merchant_name, created_at, created_by, updated_at, updated_by) values 
        (1, 1, 'merchant 1', now(), 1, now(),1), 
        (2, 2, 'Merchant 2', now(), 2, now(),2)");

        // Seed outlets
        DB::insert("insert into outlets (id, merchant_id, outlet_name, created_at, created_by, updated_at, updated_by) values 
        (1, 1, 'Outlet 1', now(), 1, now(),1), 
        (2, 2, 'Outlet 1', now(), 2, now(),2), 
        (3, 1, 'Outlet 2', now(), 1, now(),1)");

        // Seed transactions
        DB::insert("insert into transactions (id, merchant_id, outlet_id, bill_total, created_at, created_by, updated_at, updated_by) values
        (1, 1, 1, 2000, '2026-08-01 12:30:04', 1, '2026-08-01 12:30:04',1),
        (2, 1, 1, 2500, '2026-08-01 17:20:14', 1, '2026-08-01 17:20:14',1),
        (3, 1, 1, 4000, '2026-08-02 12:30:04', 1, '2026-08-02 12:30:04',1),
        (4, 1, 1, 1000, '2026-08-04 12:30:04', 1, '2026-08-04 12:30:04',1),
        (5, 1, 1, 7000, '2026-08-05 16:59:30', 1, '2026-08-05 16:59:30',1),
        (6, 1, 3, 2000, '2026-08-02 18:30:04', 1, '2026-08-02 18:30:04',1),
        (7, 1, 3, 2500, '2026-08-03 17:20:14', 1, '2026-08-03 17:20:14',1),
        (8, 1, 3, 4000, '2026-08-04 12:30:04', 1, '2026-08-04 12:30:04',1),
        (9, 1, 3, 1000, '2026-08-04 12:31:04', 1, '2026-08-04 12:31:04',1),
        (10, 1, 3, 7000, '2026-08-05 16:59:30', 1, '2026-08-05 16:59:30',1),
        (11, 2, 2, 2000, '2026-08-01 18:30:04', 2, '2026-08-01 18:30:04',2),
        (12, 2, 2, 2500, '2026-08-02 17:20:14', 2, '2026-08-02 17:20:14',2),
        (13, 2, 2, 4000, '2026-08-03 12:30:04', 2, '2026-08-03 12:30:04',2),
        (14, 2, 2, 1000, '2026-08-04 12:31:04', 2, '2026-08-04 12:31:04',2),
        (15, 2, 2, 7000, '2026-08-05 16:59:30', 2, '2026-08-05 16:59:30',2),
        (16, 2, 2, 2000, '2026-08-05 18:30:04', 2, '2026-08-05 18:30:04',2),
        (17, 2, 2, 2500, '2026-08-06 17:20:14', 2, '2026-08-06 17:20:14',2),
        (18, 2, 2, 4000, '2026-08-07 12:30:04', 2, '2026-08-07 12:30:04',2),
        (19, 2, 2, 1000, '2026-08-08 12:31:04', 2, '2026-08-08 12:31:04',2),
        (20, 2, 2, 7000, '2026-08-09 16:59:30', 2, '2026-08-09 16:59:30',2),
        (21, 2, 2, 1000, '2026-08-10 12:31:04', 2, '2026-08-10 12:31:04',2),
        (22, 2, 2, 7000, '2026-08-11 16:59:30', 2, '2026-08-11 16:59:30',2)");
    }
}
