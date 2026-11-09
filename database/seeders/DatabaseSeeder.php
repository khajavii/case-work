<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Orders\Database\Seeders\OrdersDatabaseSeeder;
use Modules\Production\Database\Seeders\ProductionDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                AuthDatabaseSeeder::class,
                OrdersDatabaseSeeder::class,
                ProductionDatabaseSeeder::class,
            ]);
        });
    }
}
