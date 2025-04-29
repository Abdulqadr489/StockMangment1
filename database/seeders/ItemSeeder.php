<?php

namespace Database\Seeders;

use App\Http\Controllers\Item\ItemController;
use App\Http\Requests\Item\ItemDetail;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'item_name' => 'Washer 21',
                'item_Barcode' => 124423232,
                'category_id' => 3,
                'item_description' => 'for a notebook',
                'item_price' => 400,
                'item_expiry_date' => '2023-12-31',
            ],
            [
                'item_name' => 'Samsung S 21',
            'item_Barcode' => 1244232,
            'category_id' => 2,
            'item_description' => 'for a notebook',
            'item_price' => 900,
            'item_expiry_date' => '2022-12-12',
            ],
            [
                'item_name' => 'Samsung TV 58',
            'item_Barcode' => 124432,
            'category_id' => 1,
            'item_description' => 'for a notebook',
            'item_price' => 400,
            'item_expiry_date' => '2021-12-31',
            ]
        ];
        $controller = new ItemController();
        $request = Request::create('/customers', 'POST', $data);

        $warehouseRequest = ItemDetail::createFrom($request);
        $warehouseRequest->setContainer(app())->setRedirector(app('redirect'));
        $warehouseRequest->validateResolved();

        $controller->store($warehouseRequest);
    }
}
