<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        ItemCategory::all();
        return response()->json([
            'message' => 'ItemCategorys retrieved successfully',
            'data' => ItemCategory::all(),
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
                'category_name' => 'required|string|max:255',
            ]);

            $ItemCategory = ItemCategory::create($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'item categories created successfully',
                'data' => $ItemCategory,
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
    public function show(ItemCategory $itemCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCategory $itemCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $category)
    {
        try{
            DB::beginTransaction();
            $validatedData = $request->validate([
                'category_name' => 'required|string|max:255',
            ]);
            $category->update($validatedData);
            DB::commit();
            return response()->json([
                'message' => 'category updated successfully',
                'data' => $category,
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
    public function destroy(ItemCategory $category)
    {
        try{
            DB::beginTransaction();
            $category->delete();
            DB::commit();
            return response()->json([
                'message' => 'category deleted successfully',
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
