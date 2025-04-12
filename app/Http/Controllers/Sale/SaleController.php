<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Sale::all();

        return response()->json([
            'message' => 'Sales retrieved successfully',
            'data' => Sale::all(),
        ], 200);
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
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'customer_id' => 'required|integer|exists:customers,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'sale_date' => 'required|date',
                'total_amount' => 'numeric|min:0',
                'items' => 'required|array',
                'items.*.item_id' => 'required|integer|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'required|numeric|min:0',
            ]);

            foreach ($validatedData['items'] as $item) {
                $stock = DB::table('branch_stocks')
                    ->where('branch_id', $validatedData['branch_id'])
                    ->where('item_id', $item['item_id'])
                    ->value('quantity');

                $itemName = DB::table('items')
                    ->where('id', $item['item_id'])
                    ->value('item_name') ?? 'Unknown Item';

                $branchName = DB::table('branches')
                ->where('id', $validatedData['branch_id'])
                ->value('branch_name') ?? 'Unknown Branch';

                if ($stock === null) {
                    throw new \Exception("Stock info not found for item '{$itemName}' in branch ID {$branchName}.");
                }

                if ($item['quantity'] > $stock) {
                    throw new \Exception("Only {$stock} units available for '{$itemName}' at branch {$branchName}.");
                }

                $sale = Sale::create([
                    'customer_id' => $validatedData['customer_id'],
                    'branch_id' => $validatedData['branch_id'],
                    'sale_date' => $validatedData['sale_date'],
                    'total_amount' => $item['quantity'] * $item['unit_price'] - $item['discount'],
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'],
                ]);

                DB::table('branch_stocks')
                    ->where('branch_id', $validatedData['branch_id'])
                    ->where('item_id', $item['item_id'])
                    ->decrement('quantity', $item['quantity']);
            }


            DB::commit();

            return response()->json([
                'message' => 'Sale created successfully',
                'data' => $sale->load('saleItems'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating sale',
                'error' => $e->getMessage(),
            ], 500);
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

     public function update(Request $request, Sale $sale)
{
    try {
        DB::beginTransaction();

        $validatedData = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'sale_date' => 'required|date',
            'total_amount' => 'numeric|min:0',
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
        ]);



        $updatedItemIds = collect($validatedData['items'])->pluck('item_id')->toArray();

        $sale->saleItems()->whereNotIn('item_id', $updatedItemIds)->each(function ($removedItem) use ($validatedData) {
            DB::table('branch_stocks')
                ->where('branch_id', $validatedData['branch_id'])
                ->where('item_id', $removedItem->item_id)
                ->increment('quantity', $removedItem->quantity);
            $removedItem->delete();
        });

        foreach ($validatedData['items'] as $item) {
            $saleItem = $sale->saleItems()->where('item_id', $item['item_id'])->first();
            $oldQuantity = $saleItem ? $saleItem->quantity : 0;
            $newQuantity = $item['quantity'];
            $quantityDiff = $newQuantity - $oldQuantity;

            $stock = DB::table('branch_stocks')
                ->where('branch_id', $validatedData['branch_id'])
                ->where('item_id', $item['item_id'])
                ->value('quantity');

            $itemName = DB::table('items')
                ->where('id', $item['item_id'])
                ->value('item_name') ?? 'Unknown Item';

            if ($stock === null) {
                throw new \Exception("Stock info not found for item '{$itemName}' in branch ID {$validatedData['branch_id']}.");
            }

            if ($quantityDiff > 0 && $quantityDiff > $stock) {
                throw new \Exception("Not enough stock for '{$itemName}'. Available: {$stock}, requested additional: {$quantityDiff}.");
            }

            if ($saleItem) {
                $saleItem->update([
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

            if ($quantityDiff != 0) {
                if ($quantityDiff > 0) {
                    DB::table('branch_stocks')
                        ->where('branch_id', $validatedData['branch_id'])
                        ->where('item_id', $item['item_id'])
                        ->decrement('quantity', $quantityDiff);
                } else {
                    DB::table('branch_stocks')
                        ->where('branch_id', $validatedData['branch_id'])
                        ->where('item_id', $item['item_id'])
                        ->increment('quantity', abs($quantityDiff));
                }
            }

            $sale->update([
                'customer_id' => $validatedData['customer_id'],
                'branch_id' => $validatedData['branch_id'],
                'sale_date' => $validatedData['sale_date'],
                'total_amount' => $item['quantity'] * $item['unit_price'] - $item['discount'],
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Sale updated successfully',
            'data' => $sale->load('saleItems'),
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
