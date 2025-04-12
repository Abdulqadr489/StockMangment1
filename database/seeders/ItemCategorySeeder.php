<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ItemCategory::create(['category_name' => 'TV']);
        ItemCategory::create(['category_name' => 'Mobile']);
        ItemCategory::create(['category_name' => 'Washer']);
    }
}
