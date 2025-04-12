<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Branch\BranchStockController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Item\ItemCategoryController;
use App\Http\Controllers\Item\ItemController;
use App\Http\Controllers\Sale\SaleController;
use App\Http\Controllers\Transfer\TransferController;
use App\Http\Controllers\WareHouse\WarehouseStockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function (){
    Route::apiResource('category',ItemCategoryController::class);

    Route::apiResource('branches',BranchController::class);

    Route::get('/branches/{id}/sales', [BranchController::class, 'getSpecificBranchSales']);

    Route::get('/sales/all/brunches', [BranchController::class, 'getAllBranchSales']);

    Route::apiResource('items',ItemController::class);

    Route::apiResource('warehouse-stock', WarehouseStockController::class);

    Route::apiResource('branch-stock', BranchStockController::class);

    Route::apiResource('customers',CustomerController::class);

    Route::apiResource('sales', SaleController::class);

    Route::apiResource('transfers', TransferController::class);



});
