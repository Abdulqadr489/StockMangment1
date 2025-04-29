<?php

namespace Database\Seeders;

use App\Http\Controllers\Customer\CustomerController;
use App\Http\Requests\Customer\CustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Ali',
            'phone' => '07712335678',
            ],
            [
                'name' => 'Rawa',
            'phone' => '07712345678',
            ],
            [
                'name' => 'karim',
            'phone' => '07712435678',
            ]
        ];
        $controller = new CustomerController();
        $request = Request::create('/customers', 'POST', $data);

        $warehouseRequest = CustomerRequest::createFrom($request);
        $warehouseRequest->setContainer(app())->setRedirector(app('redirect'));
        $warehouseRequest->validateResolved();

        $controller->store($warehouseRequest);
    }
}
