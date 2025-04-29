<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemCategory as ItemItemCategory;
use App\Models\ItemCategory;
use App\services\globalHelpers;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemCategoryController extends Controller
{

    use globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query=ItemCategory::query();

        $this->applySearch($query, $request,'category_name');

        $this->applySorting($query, $request);

        $categories = $query->paginate(10);

        return response()->json([
            'message' => 'item categories retrieved successfully',
            'data' => $categories,
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
    public function store(ItemItemCategory $request)
    {
        try {
            DB::beginTransaction();

            $ItemCategory = ItemCategory::create($request->validated());

            DB::commit();

            return $this->handleApiSuccess('Item category created successfully.', 201,[
                'data' => $ItemCategory,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleApiException($e, 'An error occurred while creating the item category.',500);
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
    public function update(ItemItemCategory $request, ItemCategory $category)
    {
        try{
            DB::beginTransaction();

            $category->update($request->validated());

            DB::commit();
            return $this->handleApiSuccess('Item category updated successfully.', 200,[
                'data' => $category,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'An error occurred while updating the item category.',500);
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
            return $this->handleApiSuccess('Item category deleted successfully.', 200,[
                'data' => $category,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'An error occurred while deleting the item category.',500);
        }

    }
}
