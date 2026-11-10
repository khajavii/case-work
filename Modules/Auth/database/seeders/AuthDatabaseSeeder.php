<?php

namespace Modules\Auth\Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $users = [
            [
                'name' => 'System Admin',
                'mobile' => '09120000001',
                'email' => 'admin@example.com',
                'role' => UserRole::ADMIN->value,
            ],
            [
                'name' => 'James Wilson',
                'mobile' => '09120000002',
                'email' => 'planner1@example.com',
                'role' => UserRole::PLANNER->value,
            ],
            [
                'name' => 'Emily Davis',
                'mobile' => '09120000003',
                'email' => 'planner2@example.com',
                'role' => UserRole::PLANNER->value,
            ],
            [
                'name' => 'Michael Brown',
                'mobile' => '09120000004',
                'email' => 'installer1@example.com',
                'role' => UserRole::INSTALLER->value,
            ],
            [
                'name' => 'Daniel Smith',
                'mobile' => '09120000005',
                'email' => 'installer2@example.com',
                'role' => UserRole::INSTALLER->value,
            ],
            [
                'name' => 'William Taylor',
                'mobile' => '09120000006',
                'email' => 'installer3@example.com',
                'role' => UserRole::INSTALLER->value,
            ],

        ];

        $password = Hash::make('password');

        User::unguarded(function () use ($users, $password): void {
            foreach ($users as $data) {
                User::query()->updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'mobile' => $data['mobile'],
                        'role' => $data['role'],
                        'password' => $password,
                        'email_verified' => now(),
                        'mobile_verified' => now(),
                    ]
                );
            }
        });
    }
}
