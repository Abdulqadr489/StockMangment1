<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Branch::all();

        return response()->json([
            'message' => 'Branches retrieved successfully',
            'data' => Branch::all(),
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
                '*.branch_name' => 'required|string|max:255',
                '*.location' => 'required|string|max:255',
            ]);

            $branches = [];

            foreach ($validatedData as $branchData) {
                $branches[] = Branch::create($branchData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Branches created successfully',
                'data' => $branches,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating branches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {

    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        try{
            DB::beginTransaction();
            $validatedData = $request->validate([
                'branch_name' => 'required|string|max:255',
                'location' => 'required|string|max:255',
            ]);
            $branch->update($validatedData);
            DB::commit();
            return response()->json([
                'message' => 'Branch updated successfully',
                'data' => $branch,
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
    public function destroy(Branch $branch)
    {
        try{
            DB::beginTransaction();
            $branch->delete();
            DB::commit();
            return response()->json([
                'message' => 'Branch deleted successfully',
            ], 200);

        }catch(ErrorException $e)
        {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSpecificBranchSales($id)
    {
        $branch = Branch::all()->findOrFail($id);

        return response()->json([
            'message' => 'Sales retrieved successfully for branch: ' . $branch->branch_name,
            'data' => $branch->sales,
        ], 200);
    }

    public function getAllBranchSales()
    {
        $branches = Branch::with(['sales','sales.customer'])->get(); // Eager load sales

        return response()->json([
            'message' => 'All branches sales retrieved successfully',
            'data' => $branches->map(function ($branch) {
                return [
                    'branch_name' => $branch->branch_name,
                    'sales' => $branch->sales->map(function ($sale) {
                        return [
                            'sale_id' => $sale->id,
                            'customer_id' => $sale->customer->name ?? null,
                            'sale_date' => $sale->sale_date,
                            'total_amount' => $sale->total_amount,
                        ];
                    }),
                ];
            }),
        ], 200);
    }

}
