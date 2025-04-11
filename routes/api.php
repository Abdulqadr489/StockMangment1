<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchStockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WarehouseStockController;
use App\Models\Customer;
use App\Models\ItemCategory;

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
