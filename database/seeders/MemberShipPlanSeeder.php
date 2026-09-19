<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberShipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => '1 Bulan',
                'duration' => 30,
                'duration_unit' => 'days',
                'price' => 100000,
                'description' => 'Akses penuh selama 1 bulan.',
                'is_active' => true,
            ],
            [
                'name' => '3 Bulan',
                'duration' => 90,
                'duration_unit' => 'days',
                'price' => 250000,
                'description' => 'Akses penuh selama 3 bulan.',
                'is_active' => true,
            ],
            [
                'name' => '6 Bulan',
                'duration' => 180,
                'duration_unit' => 'days',
                'price' => 450000,
                'description' => 'Akses penuh selama 6 bulan.',
                'is_active' => true,
            ],
            [
                'name' => '12 Bulan',
                'duration' => 365,
                'duration_unit' => 'days',
                'price' => 800000,
                'description' => 'Akses penuh selama 12 bulan.',
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\MembershipPlan::create($plan);
        }
    }
}
