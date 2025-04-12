<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
            'item_name' => 'Samsung TV 58',
            'item_Barcode' => 124432,
            'category_id' => 1,
            'item_description' => 'for a notebook',
            'item_price' => 400,
            'item_expiry_date' => '2021-12-31',
        ]);

        Item::create([
            'item_name' => 'Samsung S 21',
            'item_Barcode' => 1244232,
            'category_id' => 2,
            'item_description' => 'for a notebook',
            'item_price' => 900,
            'item_expiry_date' => '2022-12-12',
        ]);

        Item::create([
            'item_name' => 'Washer 21',
            'item_Barcode' => 124423232,
            'category_id' => 3,
            'item_description' => 'for a notebook',
            'item_price' => 400,
            'item_expiry_date' => '2023-12-31',
        ]);
    }
}
