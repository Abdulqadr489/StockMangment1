<?php

namespace Database\Seeders;

use App\Http\Controllers\Sale\SaleController;
use App\Http\Requests\Sale\SaleRequests;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample sales data
        $salesData = [
            [
                "branch_id" => 1,
                "sale_date" => "2025-04-09",
                "customer_id" => 2,
                "items" => [
                    [
                        "item_id" => 1,
                        "quantity" => 2,
                        "unit_price" => 50,
                        "discount" => 5,
                    ]
                ]
            ]
        ];

        $controller = new SaleController();

        foreach ($salesData as $data) {
            $request = Request::create('/sales', 'POST', $data);

            $transferRequest = SaleRequests::createFrom($request);
            $transferRequest->setContainer(app())->setRedirector(app('redirect'));
            $transferRequest->validateResolved();

            $controller->store($transferRequest);
        }
    }
}
