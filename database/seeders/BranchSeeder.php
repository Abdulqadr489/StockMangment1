<?php

namespace Database\Seeders;

use App\Http\Controllers\Branch\BranchController;
use App\Http\Requests\Brunch\BrunchRequest;
use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            [
                'branch_name' => 'Branch 2',
                'location' => 'duhok',
            ],
            [
                'branch_name' => 'Branch 1',
                'location' => 'duhok',
            ],
            [
                'branch_name' => 'Branch 4',
                'location' => 'duhok',
            ]
        ];
        $controller = new BranchController();
        $request = Request::create('/branches', 'POST', $data);

        $warehouseRequest = BrunchRequest::createFrom($request);
        $warehouseRequest->setContainer(app())->setRedirector(app('redirect'));
        $warehouseRequest->validateResolved();

        $controller->store($warehouseRequest);
    }
}
