<?php

namespace Database\Seeders;

use App\Models\WarehouseStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WarehouseStock::create([
            'item_id' => 1,
            'quantity' => 60,
        ]);

        WarehouseStock::create([
            'item_id' => 2,
            'quantity' => 70,
        ]);

        WarehouseStock::create([
            'item_id' => 3,
            'quantity' => 80,
        ]);
    }
}
