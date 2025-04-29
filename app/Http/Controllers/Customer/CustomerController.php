<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerRequest;
use App\Models\Customer;
use ErrorException;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{

    use \App\services\globalHelpers;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::query();
        $this->applySearch($customers, request(), 'name');
        $this->applySorting($customers, request());
        $customers = $customers->paginate(10);
        return $this->handleApiSuccess('Customers retrieved successfully', 200, [
            'data' => $customers,
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
    public function store(CustomerRequest $request)
    {
        try {
            DB::beginTransaction();

            $customers = [];
            foreach($request->validated() as $customerData) {
                $customer = Customer::create($customerData);
                $customers[] = $customer;
            }

            DB::commit();

            return $this->handleApiSuccess('Customer(s) created successfully', 201, [
                'data' => $customers,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleApiException($e, 'An error occurred while creating the customer(s)', 500, [
                'error' => $e->getMessage(),
            ]);
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
    public function update(CustomerRequest $request, Customer $customer)
    {
        try{
            DB::beginTransaction();

            $customer->update($request->validated());
            DB::commit();
            return $this->handleApiSuccess('Customer updated successfully', 200, [
                'data' => $customer,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'An error occurred while updating the customer', 500, [
                'error' => $e->getMessage(),
            ]);
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
            return $this->handleApiSuccess('Customer deleted successfully', 200, [
                'data' => $customer,
            ]);

        }catch(ErrorException $e)
        {
            return $this->handleApiException($e, 'An error occurred while deleting the customer', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
