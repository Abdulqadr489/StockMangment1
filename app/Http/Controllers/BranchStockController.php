<?php

namespace App\Http\Controllers;

use App\Models\BranchStock;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchStockController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        BranchStock::all();

        return response()->json([
            'message' => 'BranchStock retrieved successfully',
            'data' => BranchStock::all(),
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
                'item_id' => 'required|integer|exists:items,id',
                'quantity' => 'required|integer',
                'branch_id' => 'required|integer|exists:branches,id',
            ]);

            $branch = BranchStock::create($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'BranchStock created successfully',
                'data' => $branch,
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
        try{
            DB::beginTransaction();
            $validatedData = $request->validate([
              'item_id' => 'required|integer|exists:items,id',
                'quantity' => 'required|integer',
                'branch_id' => 'required|integer|exists:branches,id',
            ]);
            $branch_stock->update($validatedData);
            DB::commit();
            return response()->json([
                'message' => 'branch_stock updated successfully',
                'data' => $branch_stock,
            ], 200);

        }catch(ErrorException $e)
        {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BranchStock $branch_stock)
    {
        try{
            DB::beginTransaction();
            $branch_stock->delete();
            DB::commit();
            return response()->json([
                'message' => 'BranchStock deleted successfully',
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
