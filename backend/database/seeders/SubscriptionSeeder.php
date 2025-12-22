<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionFeature;
use App\Models\SubscriptionLimit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            'starter' => ['price' => 25, 'locations' => 3, 'cards' => 1],
            'grow' => ['price' => 45, 'locations' => 2, 'cards' => 2],
            'business' => ['price' => 60, 'locations' => 1, 'cards' => 3],
        ];

        foreach ($plans as $code => $data) {
            $plan = Subscription::create([
                'code' => $code,
                'name' => ucfirst($code),
                'price' => $data['price'],
            ]);

            SubscriptionLimit::create([
                'subscription_id' => $plan->id,
                'key' => 'locations',
                'value' => $data['locations'],
            ]);

            SubscriptionLimit::create([
                'subscription_id' => $plan->id,
                'key' => 'cards',
                'value' => $data['cards'],
            ]);
        }
    }
}
