<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Customer::all();

        return response()->json([
            'message' => 'Customers retrieved successfully',
            'data' => Customer::all(),
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
                    '*.name' => 'required|string|max:255',
                    '*.phone' => 'required|string|max:15',
                ]
                : [
                    'name' => 'required|string|max:255',
                    'phone' => 'required|string|max:15',
                ];

            $validatedData = $request->validate($rules);

            $customers = [];

            if ($isMultiple) {
                foreach ($validatedData as $customerData) {
                    $customers[] = Customer::create($customerData);
                }
            } else {
                $customers[] = Customer::create($validatedData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Customer(s) created successfully',
                'data' => $customers,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating customer(s)',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try{
            DB::beginTransaction();
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
            ]);
            $customer->update($validatedData);
            DB::commit();
            return response()->json([
                'message' => 'Customer updated successfully',
                'data' => $customer,
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
    public function destroy(Customer $customer)
    {
        try{
            DB::beginTransaction();
            $customer->delete();
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
}
