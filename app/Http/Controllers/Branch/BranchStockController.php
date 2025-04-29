<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\services\globalHelpers;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class BranchStockController extends Controller
{

    use globalHelpers;
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = BranchStock::with(['item', 'branch']);

        $this->applySearch($query, request(), 'branch_name', 'branch');
        $this->applySorting($query, request());

        $per_page = request()->input('per_page', 10);
        $data = $query->paginate($per_page);

        return $this->handleApiSuccess('BranchStock fetched successfully', 200, [
            'data' => $data->getCollection()->map(function ($stock) {
                return [
                    'branch_name' => $stock->branch?->branch_name,
                    'item_name' => $stock->item?->item_name,
                    'quantity' => $stock->quantity,
                ];
            }),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
            ]
        ]);
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

    }

    /**
     * Display the specified resource.
     */
    public function show(BranchStock $branchStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BranchStock $branchStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BranchStock $branch_stock)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BranchStock $branch_stock)
    {

    }
}
