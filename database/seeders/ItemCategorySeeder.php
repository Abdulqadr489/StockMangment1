<?php

namespace Database\Seeders;

use App\Http\Controllers\Item\ItemCategoryController;
use App\Http\Controllers\Item\ItemController;
use App\Http\Requests\Item\ItemCategory as ItemItemCategory;
use App\Models\ItemCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'category_name' => 'TV'
            ],
            [
                'category_name' => 'TV1'

            ],
            [
                'category_name' => 'TV2'

            ]
        ];
        $controller = new ItemCategoryController();
        $request = Request::create('/category', 'POST', $data);

        $warehouseRequest = ItemItemCategory::createFrom($request);
        $warehouseRequest->setContainer(app())->setRedirector(app('redirect'));
        $warehouseRequest->validateResolved();

        $controller->store($warehouseRequest);
    }
}
