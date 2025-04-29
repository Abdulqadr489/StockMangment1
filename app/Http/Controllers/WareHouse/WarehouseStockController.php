<?php

namespace App\Http\Controllers\WareHouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\WareHouse\WareHouseRequest;
use App\Models\WarehouseStock;
use ErrorException;
use Illuminate\Support\Facades\DB;

class WarehouseStockController extends Controller
{
    use \App\services\globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouseStocks = WarehouseStock::query();

        $this->applySearch($warehouseStocks, request(), 'item_name', 'item');
        $this->applySorting($warehouseStocks, request(),'item_id');

        $data = $warehouseStocks->paginate(10);

        return $this->handleApiSuccess(
            'Warehouse stocks retrieved successfully',
            200,
            [
                'data' => $data->getCollection()->map(function ($stock) {
                    return [
                        'item_name' => $stock->item?->item_name,
                        'quantity' => $stock->quantity,
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
    public function store(WareHouseRequest $request)
    {
        try {
            DB::beginTransaction();

            $warehouseStocks = [];

            foreach ($request->validated() as $stockData) {
                $warehouseStocks[] = WarehouseStock::create($stockData);
            }

            DB::commit();
            return $this->handleApiSuccess(
                'Warehouse stocks created successfully',
                201,
                [
                    'data' => $warehouseStocks,
                ]
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException(
                $e,
                'Failed to create warehouse stocks',
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
    public function show(WarehouseStock $warehouseStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WarehouseStock $warehouseStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WareHouseRequest $request, WarehouseStock $warehouse_stock)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $warehouse_stock->update($validatedData);
            DB::commit();
            return $this->handleApiSuccess(
                'WarehouseStock updated successfully',
                200,
                [
                    'data' => $warehouse_stock,
                ]
            );
        } catch (ErrorException $e) {
            $this->handleApiException(
                $e,
                'Failed to update warehouse stock',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
            DB::rollBack();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseStock $warehouse_stock)
    {
        try {
            DB::beginTransaction();
            $warehouse_stock->delete();
            DB::commit();
            return $this->handleApiSuccess(
                'WarehouseStock deleted successfully',
                200,
                [
                    'data' => $warehouse_stock,
                ]
            );
        } catch (ErrorException $e) {
            return $this->handleApiException(
                $e,
                'Failed to delete warehouse stock',
                500,
                [
                    'error' => $e->getMessage(),
                ]
            );
            DB::rollBack();
        }
    }
}
