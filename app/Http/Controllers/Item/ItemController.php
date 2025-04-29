<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemDetail;
use App\Models\Item;
use App\services\globalHelpers;
use ErrorException;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{

    use globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Item::query();

        $this->applySearch($query, request(), 'item_name');
        $this->applySorting($query, request());
        $items = $query->paginate(10);

        return $this->handleApiSuccess('Items retrieved successfully', 200, [
            'data' => $items,
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
    public function store(ItemDetail $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $isMultiple = is_array($data) && isset($data[0]);

            $items = [];

            if ($isMultiple) {
                foreach($data as $itemData){
                    $items[] = Item::create($itemData);
                }
            } else {
                $items[] = Item::create($data);
            }

            DB::commit();

            return $this->handleApiSuccess('Item(s) created successfully', 201, [
                'data' => $items,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException($e, 'An error occurred while processing your request.', 500, [
                'error' => $e->getMessage(),
            ]);
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
    public function update(ItemDetail $request, Item $item)
    {
        try {
            DB::beginTransaction();


            $item->update($request->validated());
            DB::commit();

            return $this->handleApiSuccess('Item updated successfully', 200, [
                'data' => $item,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleApiException($e, 'An error occurred while processing your request.', 500, [
                'error' => $e->getMessage(),
            ]);
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
            return $this->handleApiSuccess('Item deleted successfully', 200, [
                'data' => $item,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'An error occurred while processing your request.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
