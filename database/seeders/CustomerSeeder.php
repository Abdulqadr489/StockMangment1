<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            "name" => 'Ali',
            "phone" => '07712345678',
        ]);

        Customer::create([
            "name" => 'rawa',
            "phone" => '07713456789',
        ]);

        Customer::create([
            "name" => 'karim',
            "phone" => '07501234567',
        ]);
    }
}
