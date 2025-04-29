<?php

namespace Database\Seeders;

use App\Http\Controllers\WareHouse\WarehouseStockController;
use App\Http\Requests\WareHouse\WareHouseRequest;
use App\Models\WarehouseStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class WarehouseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        $datas = [
            [
                'item_id' => 1,
                'quantity' => 60,
            ],
            [
                'item_id' => 2,
                'quantity' => 30,
            ],
            [
                'item_id' => 3,
                'quantity' => 20,
            ]
        ];

        $controller = new WarehouseStockController();
        $request = Request::create('/warehouse-stock', 'POST', $datas);

        $warehouseRequest = WareHouseRequest::createFrom($request);
        $warehouseRequest->setContainer(app())->setRedirector(app('redirect'));
        $warehouseRequest->validateResolved();

        $controller->store($warehouseRequest);

    }

}
