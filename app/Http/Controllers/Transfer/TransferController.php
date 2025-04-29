<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\TransferRequest;
use App\Models\Transfer;
use ErrorException;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{

    use \App\services\globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Transfer::query();
        $this->applySearch($data, request(), 'branch_name', 'brunch');
        $this->applySorting($data, request());
        $per_page = request()->input('per_page', 10);
        $data = $data->paginate($per_page);
        return $this->handleApiSuccess(
            'Transfer list retrieved successfully',
            200,
            [
                'data' => $data->getCollection()->map(function ($transfer) {
                    return [
                        'id' => $transfer->id,
                        'branch_name' => $transfer->brunch->branch_name ?? 'N/A',
                        'transfer_date' => $transfer->transfer_date,
                        'status' => $transfer->status,

                    ];
                })
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $transfer = Transfer::create([
                'brunch_id' => $validatedData['branch_id'],
                'transfer_date' => $validatedData['transfer_date'],
            ]);

            foreach ($validatedData['transfers'] as $item) {
                $stock = DB::table('warehouse_stocks')
                    ->where('item_id', $item['item_id'])
                    ->value('quantity');

                if ($stock === null || $stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item ID {$item['item_id']}. Available: {$stock}");
                }
                $createStock = $transfer->brunchStock()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);


                if (!$createStock) {
                    throw new \Exception("Failed to create branch stock.");
                }

                $transfer->transferItems()->create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);

                DB::table('warehouse_stocks')
                    ->where('item_id', $item['item_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            return $this->handleApiSuccess(
                'Transfer created successfully',
                201,

            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException(
                $e,
                'An error occurred while processing your request.',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Transfer $branch) {}

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
    public function update(TransferRequest $request, Transfer $transfer)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Update transfer info
            $transfer->update([
                'brunch_id' => $validatedData['branch_id'],
                'transfer_date' => $validatedData['transfer_date'],
            ]);

            // Get updated item IDs
            $updatedItemIds = collect($validatedData['transfers'])->pluck('item_id')->toArray();

            // Delete removed transfer items and return their quantities to the warehouse
            $transferItemsToDelete = $transfer->transferItems()->whereNotIn('item_id', $updatedItemIds)->get();
            foreach ($transferItemsToDelete as $itemToDelete) {
                // Return the quantity of the removed item to warehouse stock
                DB::table('warehouse_stocks')
                    ->where('item_id', $itemToDelete->item_id)
                    ->increment('quantity', $itemToDelete->quantity);

                // Also decrement the quantity from the branch stock for the old branch
                DB::table('branch_stocks')
                    ->where('branch_id', $transfer->brunch_id)
                    ->where('item_id', $itemToDelete->item_id)
                    ->decrement('quantity', $itemToDelete->quantity);
            }

            // Preload current transfer items
            $currentTransferItems = $transfer->transferItems()->get()->keyBy('item_id');

            // Preload warehouse stock
            $warehouseStocks = DB::table('warehouse_stocks')
                ->whereIn('item_id', $updatedItemIds)
                ->pluck('quantity', 'item_id');

            // Preload branch stock for both old and new branch
            $branchStocks = DB::table('branch_stocks')
                ->whereIn('item_id', $updatedItemIds)
                ->whereIn('branch_id', [$validatedData['branch_id'], $transfer->brunch_id])
                ->pluck('quantity', 'item_id', 'branch_id');

            // Detect if branch_id has changed
            $oldBranchId = $transfer->brunch_id;
            $newBranchId = $validatedData['branch_id'];
            $isBranchChanged = $oldBranchId !== $newBranchId;

            foreach ($validatedData['transfers'] as $item) {
                $itemId = $item['item_id'];
                $newQuantity = $item['quantity'];

                $transferItem = $currentTransferItems->get($itemId);
                $oldQuantity = $transferItem ? $transferItem->quantity : 0;

                // Calculate the quantity difference
                $quantityDifference = $newQuantity - $oldQuantity;

                // Get the warehouse stock for the item
                $stock = $warehouseStocks[$itemId] ?? null;

                // Check if there's enough stock in the warehouse
                if ($stock === null || ($quantityDifference > 0 && $stock < $quantityDifference)) {
                    throw new \Exception("Insufficient warehouse stock for item ID {$itemId}. Available: {$stock}");
                }

                // Handle stock changes if the branch is changed
                if ($isBranchChanged) {
                    // Step 1: Decrease stock from the old branch (if quantity exists)
                    if ($oldQuantity > 0) {
                        DB::table('branch_stocks')
                            ->where('branch_id', $oldBranchId)
                            ->where('item_id', $itemId)
                            ->decrement('quantity', $oldQuantity);
                    }

                    // Step 2: Check if the item exists in the new branch
                    $newBranchStock = DB::table('branch_stocks')
                        ->where('branch_id', $newBranchId)
                        ->where('item_id', $itemId)
                        ->first();

                    if ($newBranchStock) {
                        // If the item exists in the new branch, increment the quantity
                        DB::table('branch_stocks')
                            ->where('branch_id', $newBranchId)
                            ->where('item_id', $itemId)
                            ->increment('quantity', $newQuantity);
                    } else {
                        // If the item does not exist in the new branch, create a new record with the new quantity
                        DB::table('branch_stocks')->insert([
                            'branch_id' => $newBranchId,
                            'item_id' => $itemId,
                            'quantity' => $newQuantity,
                        ]);
                    }

                    // Step 3: Decrease warehouse stock based on the transferred quantity
                    DB::table('warehouse_stocks')
                        ->where('item_id', $itemId)
                        ->decrement('quantity', $newQuantity);
                } else {
                    // If the branch is the same, just adjust the quantity in the existing branch stock
                    if ($quantityDifference > 0) {
                        DB::table('branch_stocks')
                            ->where('branch_id', $newBranchId)
                            ->where('item_id', $itemId)
                            ->increment('quantity', $quantityDifference);

                        DB::table('warehouse_stocks')
                            ->where('item_id', $itemId)
                            ->decrement('quantity', $quantityDifference);
                    } elseif ($quantityDifference < 0) {
                        DB::table('branch_stocks')
                            ->where('branch_id', $newBranchId)
                            ->where('item_id', $itemId)
                            ->decrement('quantity', abs($quantityDifference));

                        DB::table('warehouse_stocks')
                            ->where('item_id', $itemId)
                            ->increment('quantity', abs($quantityDifference));
                    }
                }

                // Update or create transfer item
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

            return $this->handleApiSuccess(
                'Transfer updated successfully',
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException(
                $e,
                'An error occurred while processing your request.',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }









    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transfer $transfer)
    {
        try {
            DB::beginTransaction();
            $transfer->delete();
            DB::commit();
            return $this->handleApiSuccess(
                'Transfer deleted successfully',
                200,
            );
        } catch (ErrorException $e) {
            return $this->handleApiException(
                $e,
                'An error occurred while processing your request.',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
