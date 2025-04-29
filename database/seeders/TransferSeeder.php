<?php

namespace Database\Seeders;

use App\Http\Controllers\Transfer\TransferController;
use App\Http\Requests\Transfer\TransferRequest;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        // Define your transfer data
        $transfersData = [
            [
                'branch_id' => 1,
                'transfer_date' => '2022-02-23',
                'transfers' => [
                    ['item_id' => 1, 'quantity' => 5],
                ],
            ],
            [
                'branch_id' => 2,
                'transfer_date' => '2022-02-24',
                'transfers' => [
                    ['item_id' => 2, 'quantity' => 10],
                ],
            ],
        ];

        foreach ($transfersData as $transferData) {
            $request = Request::create('/transfers', 'POST', $transferData);

            $transferRequest = TransferRequest::createFrom($request);
            $transferRequest->setContainer(app())->setRedirector(app('redirect'));

            $transferRequest->validateResolved();

            $controller = new TransferController();
            $controller->store($transferRequest);
        }
    }
}
