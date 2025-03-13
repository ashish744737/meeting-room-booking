<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('subscription_plans')->insert([
            ['name' => 'basic', 'price' => 100, 'duration' => 30, 'bookings_per_day' => 5],
            ['name' => 'advance', 'price' => 200, 'duration' => 30, 'bookings_per_day' => 7],
            ['name' => 'premium', 'price' => 300, 'duration' => 30, 'bookings_per_day' => 10],
        ]);
    }
}
