<?php

namespace App\Http\Controllers;

use App\Models\BranchStock;
use App\Models\Transfer;
use App\Models\TransferItem;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Transfer::all();

        return response()->json([
            'message' => 'transfer retrieved successfully',
            'data' => Transfer::all(),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'brunch_id' => 'required|integer|exists:branches,id',
                'transfer_date' => 'required|date',
                'transfers' => 'required|array',

                'transfers*.item_id' => 'required|integer|exists:items,id',
                'transfers*.quantity' => 'required|integer|min:1',
                'transfers*.transfer_id' => 'required|numeric|min:0',
            ]);

            $transfer = Transfer::create([
                'brunch_id' => $validatedData['brunch_id'],
                'transfer_date' => $validatedData['transfer_date'],
            ]);



            foreach ($validatedData['transfers'] as $item) {
                $stock = DB::table('warehouse_stocks')
                    ->where('item_id', $item['item_id'])
                    ->value('quantity');

                if ($stock === null || $stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item ID {$item['item_id']}. Available: {$stock}");
                }

                $stockBrunch=BranchStock::create([
                    'branch_id' => $validatedData['brunch_id'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
                if (!$stockBrunch) {
                    throw new \Exception("Failed to create branch stock.");
                }

                $transfer->transferItems()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);

                DB::table('warehouse_stocks')
                    ->where('item_id', $item['item_id'])
                    ->decrement('quantity', $item['quantity']);
            }




            DB::commit();

            return response()->json([
                'message' => 'Transfer created successfully',
                'data' => $transfer,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating branch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Transfer $branch)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transfer $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transfer $transfer)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'brunch_id' => 'required|integer|exists:branches,id',
                'transfer_date' => 'required|date',
                'transfers' => 'required|array',
                'transfers.*.item_id' => 'required|integer|exists:items,id',
                'transfers.*.quantity' => 'required|integer|min:1',
            ]);

            $transfer->update([
                'brunch_id' => $validatedData['brunch_id'],
                'transfer_date' => $validatedData['transfer_date'],
            ]);

            $updatedItemIds = collect($validatedData['transfers'])->pluck('item_id')->toArray();

            $transfer->transferItems()->whereNotIn('item_id', $updatedItemIds)->delete();

            foreach ($validatedData['transfers'] as $item) {
                $itemId = $item['item_id'];
                $newQuantity = $item['quantity'];

                $transferItem = $transfer->transferItems()->where('item_id', $itemId)->first();
                $oldQuantity = $transferItem ? $transferItem->quantity : 0;

                $quantityDifference = $newQuantity - $oldQuantity;

                $stock = DB::table('warehouse_stocks')
                    ->where('item_id', $itemId)
                    ->value('quantity');

                if ($stock === null || ($quantityDifference > 0 && $stock < $quantityDifference)) {
                    throw new \Exception("Insufficient warehouse stock for item ID {$itemId}. Available: {$stock}");
                }

                $branchStock = DB::table('branch_stocks')
                    ->where('item_id', $itemId)
                    ->where('branch_id', $validatedData['brunch_id'])
                    ->first();

                if (!$branchStock) {
                    DB::table('branch_stocks')->insert([
                        'branch_id' => $validatedData['brunch_id'],
                        'item_id' => $itemId,
                        'quantity' => 0,
                    ]);
                }

                if ($quantityDifference > 0) {
                    DB::table('warehouse_stocks')
                        ->where('item_id', $itemId)
                        ->decrement('quantity', $quantityDifference);

                    DB::table('branch_stocks')
                        ->where('item_id', $itemId)
                        ->where('branch_id', $validatedData['brunch_id'])
                        ->increment('quantity', $quantityDifference);
                } elseif ($quantityDifference < 0) {
                    DB::table('warehouse_stocks')
                        ->where('item_id', $itemId)
                        ->increment('quantity', abs($quantityDifference));

                    DB::table('branch_stocks')
                        ->where('item_id', $itemId)
                        ->where('branch_id', $validatedData['brunch_id'])
                        ->decrement('quantity', abs($quantityDifference));
                }

                if ($transferItem) {
                    $transferItem->update([
                        'quantity' => $newQuantity,
                    ]);
                } else {
                    $transfer->transferItems()->create([
                        'item_id' => $itemId,
                        'quantity' => $newQuantity,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Transfer updated successfully',
                'data' => $transfer->load('transferItems'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        try{
            DB::beginTransaction();
            $transfer->delete();
            DB::commit();
            return response()->json([
                'message' => 'transfer deleted successfully',
            ], 200);

        }catch(ErrorException $e)
        {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
