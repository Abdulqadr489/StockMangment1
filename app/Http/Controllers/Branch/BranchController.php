<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brunch\BrunchRequest;
use App\Models\Branch;
use App\services\globalHelpers;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{

    use globalHelpers;

    public function index(Request $request)
    {
        $query = Branch::query();

        $this->applySearch($query, $request, 'branch_name');
        $this->applySorting($query, $request);
        $branches = $query->paginate(10);
        return $this->handleApiSuccess('Branches retrieved successfully', 200, [
            'data' => $branches,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }


    public function store(BrunchRequest $request)
    {
        try {
            DB::beginTransaction();

            $branches = [];

            foreach ($request->validated() as $branchData) {
                $branches[] = Branch::create($branchData);
            }

            DB::commit();

            return $this->handleApiSuccess('Branches created successfully', 201, [
                'data' => $branches,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException($e, 'Failed to create branches', 500, [
                'error' => $e->getMessage(),
            ]);
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
    public function update(BrunchRequest $request, Branch $branch)
    {
        try{

            DB::beginTransaction();

            $branch->update($request->validated());
            DB::commit();
            return $this->handleApiSuccess('Branch updated successfully', 200, [
                'data' => $branch,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'Failed to update branch', 500, [
                'error' => $e->getMessage(),
            ]);
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
            return $this->handleApiSuccess('Branch deleted successfully', 200, [
                'data' => $branch,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'Failed to delete branch', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getSpecificBranchSales($id)
    {
        $branch = Branch::all()->findOrFail($id);

        return $this->handleApiSuccess('Branch sales retrieved successfully', 200, [
           'message' => 'Sales retrieved successfully for branch: ' . $branch->branch_name,
            'data' => $branch->sales,
        ]);

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

        return $this->handleApiSuccess('All branches sales retrieved successfully', 200, [
            'branch_name' => $branch->branch_name,
                    'sales' => $branch->sales->map(function ($sale) {
                        return [
                            'sale_id' => $sale->id,
                            'customer_id' => $sale->customer->name ?? null,
                            'sale_date' => $sale->sale_date,
                            'total_amount' => $sale->total_amount,
                        ];
                    }),
        ]);
    }

}
