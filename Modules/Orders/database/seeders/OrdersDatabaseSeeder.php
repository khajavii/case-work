<?php

namespace Modules\Orders\Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

class OrdersDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = [
            [
                'owner_email' => 'admin@example.com',
                'customer_name' => 'Oliver Anderson',
                'address' => '12 Oak Street, Springfield',
                'status' => OrderStatus::PENDING->value,
            ],
            [
                'owner_email' => 'admin@example.com',
                'customer_name' => 'Sophia Martin',
                'address' => '24 Maple Avenue, Springfield',
                'status' => OrderStatus::IN_PROGRESS->value,
            ],
            [
                'owner_email' => 'planner1@example.com',
                'customer_name' => 'Lucas Thompson',
                'address' => '8 Cedar Lane, Riverside',
                'status' => OrderStatus::IN_PROGRESS->value,
            ],
            [
                'owner_email' => 'planner1@example.com',
                'customer_name' => 'Charlotte White',
                'address' => '31 Pine Road, Riverside',
                'status' => OrderStatus::COMPLETED->value,
            ],
            [
                'owner_email' => 'planner2@example.com',
                'customer_name' => 'Benjamin Harris',
                'address' => '17 Elm Street, Brookfield',
                'status' => OrderStatus::IN_PROGRESS->value,
            ],
            [
                'owner_email' => 'planner2@example.com',
                'customer_name' => 'Amelia Clark',
                'address' => '45 Willow Drive, Brookfield',
                'status' => OrderStatus::COMPLETED->value,
            ],
        ];

        Order::unguarded(function () use ($orders): void {
            foreach ($orders as $data) {
                $owner = User::query()
                    ->where('email', $data['owner_email'])
                    ->whereIn('role', [
                        UserRole::ADMIN->value,
                        UserRole::PLANNER->value,
                    ])
                    ->firstOrFail();

                Order::query()->updateOrCreate(
                    [
                        'owner_id' => $owner->id,
                        'customer_name' => $data['customer_name'],
                    ],
                    [
                        'address' => $data['address'],
                        'status' => $data['status'],
                    ]
                );
            }
        });
    }
}
