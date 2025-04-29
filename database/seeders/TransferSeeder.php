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
        $transfersData = [
            [
                'branch_id' => 1,
                'transfer_date' => '2022-02-23',
                'transfers' => [
                    ['item_id' => 1, 'quantity' => 5],
                    ['item_id' => 2, 'quantity' => 5],
                ],
            ],


        ];

        $controller = new TransferController();

        foreach ($transfersData as $data) {
            $request = Request::create('/transfers', 'POST', $data);

            $transferRequest = TransferRequest::createFrom($request);
            $transferRequest->setContainer(app())->setRedirector(app('redirect'));
            $transferRequest->validateResolved();

            $controller->store($transferRequest);
        }
    }
}
