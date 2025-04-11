<?php

namespace App\Http\Controllers;

use App\Models\Item;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Item::all();

        return response()->json([
            'message' => 'items retrieved successfully',
            'data' => Item::all(),
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

            $data = $request->all();
            $isMultiple = is_array($data) && isset($data[0]);

            $rules = $isMultiple
                ? [
                    '*.item_name' => 'required|string|max:255',
                    '*.item_Barcode' => 'required|numeric|unique:items,item_Barcode',
                    '*.category_id' => 'required|integer|exists:item_categories,id',
                    '*.item_description' => 'nullable|string',
                    '*.item_price' => 'required|numeric|min:0',
                    '*.item_expiry_date' => 'required|date',
                ]
                : [
                    'item_name' => 'required|string|max:255',
                    'item_Barcode' => 'required|numeric|unique:items,item_Barcode',
                    'category_id' => 'required|integer|exists:item_categories,id',
                    'item_description' => 'nullable|string',
                    'item_price' => 'required|numeric|min:0',
                    'item_expiry_date' => 'required|date',
                ];

            $validatedData = $request->validate($rules);

            $items = [];

            if ($isMultiple) {
                foreach ($validatedData as $itemData) {
                    $items[] = Item::create($itemData);
                }
            } else {
                $items[] = Item::create($validatedData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Item(s) created successfully',
                'data' => $items,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating item(s)',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'item_name' => 'required|string|max:255',
                'item_Barcode' => 'required|max:255|unique:items,item_Barcode,' . $item->id,
                'category_id' => 'required|exists:item_categories,id',
                'item_description' => 'nullable|string|max:1000',
                'item_price' => 'required|numeric|min:0',
                'item_expiry_date' => 'nullable|date',
            ]);

            $item->update($validatedData);
            DB::commit();

            return response()->json([
                'message' => 'Item updated successfully',
                'data' => $item,
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
    public function destroy(Item $item)
    {
        try{
            DB::beginTransaction();
            $item->delete();
            DB::commit();
            return response()->json([
                'message' => 'Item deleted successfully',
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
