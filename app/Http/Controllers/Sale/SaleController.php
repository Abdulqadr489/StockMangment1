<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\SaleRequests;
use App\Models\Sale;
use App\Models\SaleItem;
use App\services\globalHelpers;
use ErrorException;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    use globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Sale::query();
        $this->applySearch($data, request(), 'name', 'customer');
        $this->applySorting($data, request());
        $per_page = request()->input('per_page', 10);
        $data = $data->paginate($per_page);
        return $this->handleApiSuccess(
            'Sales list retrieved successfully',
            200,
            [
                'data' => $data->getCollection()->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'customer' => $sale->customer->name ?? 'N/A',
                        'Phone' => $sale->customer->phone ?? 'N/A',
                        'item' => $sale->saleItems->map(function ($item) {
                            return [
                                'item_name' => $item->item->item_name ?? 'N/A',
                                'quantity' => $item->quantity ?? 'N/A',
                                'unit_price' => $item->unit_price ?? 'N/A',
                                'discount' => $item->discount ?? 'N/A',
                            ];
                        }),

                    ];
                })
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequests $request)
    {
        try {
            DB::beginTransaction();

            $validatedSales = $request->validated();

            foreach ($validatedSales as $saleData) {
                $branchId = $saleData['branch_id'];

                $sale = Sale::create([
                    'customer_id' => $saleData['customer_id'],
                    'branch_id' => $branchId,
                    'sale_date' => $saleData['sale_date'],
                    'total_amount' => 0,
                ]);

                $totalAmount = 0;

                foreach ($saleData['items'] as $item) {
                    $stock = DB::table('branch_stocks')
                        ->where('branch_id', $branchId)
                        ->where('item_id', $item['item_id'])
                        ->value('quantity');

                    $itemName = DB::table('items')
                        ->where('id', $item['item_id'])
                        ->value('item_name') ?? 'Unknown Item';

                    $branchName = DB::table('branches')
                        ->where('id', $branchId)
                        ->value('branch_name') ?? 'Unknown Branch';

                    if ($stock === null) {
                        throw new \Exception("Stock info not found for item '{$itemName}' in branch '{$branchName}'.");
                    }

                    if ($item['quantity'] > $stock) {
                        throw new \Exception("Only {$stock} units available for '{$itemName}' at branch '{$branchName}'.");
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                    ]);

                    DB::table('branch_stocks')
                        ->where('branch_id', $branchId)
                        ->where('item_id', $item['item_id'])
                        ->decrement('quantity', $item['quantity']);

                    $lineAmount = ($item['quantity'] * $item['unit_price']) - $item['discount'];
                    $totalAmount += $lineAmount;
                }

                $sale->update([
                    'total_amount' => $totalAmount,
                ]);
            }

            DB::commit();

            return $this->handleApiSuccess(
                'Sales created successfully',
                201,
                [
                    'data' => Sale::with('saleItems')->latest()->get(), // return all recent sales
                ]
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
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

     public function update(SaleRequests $request, Sale $sale)
{
    try {
        DB::beginTransaction();

        $validatedSales = $request->validated();

        foreach ($validatedSales as $saleData) {

            $oldBranchId = $sale->branch_id;
            $newBranchId = $saleData['branch_id'];

            if ($oldBranchId !== $newBranchId) {
                foreach ($sale->saleItems as $oldItem) {
                    DB::table('branch_stocks')
                        ->where('branch_id', $oldBranchId)
                        ->where('item_id', $oldItem->item_id)
                        ->whereNull('deleted_at')
                        ->increment('quantity', $oldItem->quantity);
                }
                $sale->saleItems()->whereNull('deleted_at')->delete();
            }

            $sale->update([
                'customer_id' => $saleData['customer_id'],
                'branch_id' => $newBranchId,
                'sale_date' => $saleData['sale_date'],
            ]);

            $totalAmount = 0;

            $existingSaleItems = $sale->saleItems()->whereNull('deleted_at')->get()->keyBy('item_id');

            $newItemIds = collect($saleData['items'])->pluck('item_id')->toArray();
            $oldItemIds = $existingSaleItems->keys()->toArray();
            $removedItemIds = array_diff($oldItemIds, $newItemIds);

            foreach ($removedItemIds as $removedItemId) {
                $removedItem = $existingSaleItems[$removedItemId];

                DB::table('branch_stocks')
                    ->where('branch_id', $oldBranchId)
                    ->where('item_id', $removedItemId)
                    ->whereNull('deleted_at')
                    ->increment('quantity', $removedItem->quantity);

                $removedItem->delete();
            }

            foreach ($saleData['items'] as $item) {
                $existingItem = $sale->saleItems()->where('item_id', $item['item_id'])->whereNull('deleted_at')->first();
                $oldQuantity = $existingItem ? $existingItem->quantity : 0;
                $newQuantity = $item['quantity'];
                $quantityDiff = $newQuantity - $oldQuantity;

                $totalStock = DB::table('branch_stocks')
                    ->where('branch_id', $newBranchId)
                    ->where('item_id', $item['item_id'])
                    ->whereNull('deleted_at')
                    ->sum('quantity');

                if ($totalStock === null) {
                    throw new \Exception("Item {$item['item_id']} not found in branch {$newBranchId} stock.");
                }

                if ($quantityDiff > 0 && $quantityDiff > $totalStock) {
                    throw new \Exception("Not enough stock for item {$item['item_id']} in branch {$newBranchId}. Available: {$totalStock}, Needed: {$quantityDiff}");
                }

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $newQuantity,
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                    ]);
                } else {
                    $sale->saleItems()->create([
                        'item_id' => $item['item_id'],
                        'quantity' => $newQuantity,
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                    ]);
                }

                if ($quantityDiff > 0) {
                    $remainingQuantityToDecrement = $quantityDiff;
                    $stocks = DB::table('branch_stocks')
                        ->where('branch_id', $newBranchId)
                        ->where('item_id', $item['item_id'])
                        ->whereNull('deleted_at')
                        ->orderBy('created_at')
                        ->get();

                    foreach ($stocks as $stock) {
                        $decrement = min($stock->quantity, $remainingQuantityToDecrement);
                        DB::table('branch_stocks')
                            ->where('id', $stock->id)
                            ->decrement('quantity', $decrement);
                        $remainingQuantityToDecrement -= $decrement;

                        if ($remainingQuantityToDecrement <= 0) {
                            break;
                        }
                    }

                    if ($remainingQuantityToDecrement > 0) {
                        throw new \Exception("Not enough stock available for item {$item['item_id']} after attempting to decrement.");
                    }
                } elseif ($quantityDiff < 0) {
                    DB::table('branch_stocks')
                        ->where('branch_id', $newBranchId)
                        ->where('item_id', $item['item_id'])
                        ->whereNull('deleted_at')
                        ->increment('quantity', abs($quantityDiff));
                }

                $lineAmount = ($newQuantity * $item['unit_price']) - $item['discount'];
                $totalAmount += $lineAmount;
            }

            $sale->update([
                'total_amount' => $totalAmount,
            ]);
        }

        DB::commit();

        return $this->handleApiSuccess(
            'Sale(s) updated successfully',
            200,
            [
                'data' => Sale::with('saleItems')->find($sale->id),  // Return the updated sale with sale items
            ]
        );

    } catch (\Exception $e) {
        DB::rollBack();

        return $this->handleApiException(
            $e,
            'An error occurred while updating the sale.',
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
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();
            $sale->delete();
            DB::commit();
            return response()->json([
                'message' => 'Branch deleted successfully',
            ], 200);
        } catch (ErrorException $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
