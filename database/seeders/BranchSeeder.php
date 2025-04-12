<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            "branch_name" => 'Branch 1',
            "location" => 'Erbil',
        ]);

        Branch::create([
            "branch_name" => 'Branch 2',
            "location" => 'duhok',
        ]);

        Branch::create([
            "branch_name" => 'Branch 3',
            "location" => 'kakruk',
        ]);
    }
}
