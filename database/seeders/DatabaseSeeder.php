<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Transfer;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       $this->call([
           ItemCategorySeeder::class,
           ItemSeeder::class,
           BranchSeeder::class,
           CustomerSeeder::class,
           WarehouseStockSeeder::class,
           TransferSeeder::class,
           SaleSeeder::class,
        ]);
    }
}
