<?php

namespace Database\Seeders;

use App\Http\Controllers\TransferController;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class TransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transfersData = [
            [
                'brunch_id' => 1,
                'transfer_date' => '2022-02-23',
                'transfers' => [
                    ['item_id' => 1, 'quantity' => 5],
                    ['item_id' => 2, 'quantity' => 5],
                ],
            ],
            [
                'brunch_id' => 2,
                'transfer_date' => '2022-02-24',
                'transfers' => [
                    ['item_id' => 2, 'quantity' => 5],
                    ['item_id' => 3, 'quantity' => 7],
                ],
            ],
            [
                'brunch_id' => 3,
                'transfer_date' => '2022-02-25',
                'transfers' => [
                    ['item_id' => 1, 'quantity' => 10],
                    ['item_id' => 3, 'quantity' => 3],
                ],
            ],
        ];

        foreach ($transfersData as $data) {
            $request = Request::create('/transfers', 'POST', $data);

            $transferController = new TransferController();

            $transferController->store($request);
        }
    }

}
