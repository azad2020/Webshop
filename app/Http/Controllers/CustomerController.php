<?php

namespace App\Http\Controllers;

use App\Exceptions\GeneralJsonException;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;


class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(Request $request)
    {
        $pageSize = $request->page_size ?? 20;
        $customers = Customer::query()->paginate($pageSize);

        return CustomerResource::collection($customers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return CustomerResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'registered_since' => 'required|date',
            'phone' => 'required|string|max:20',
        ], [
            'job_title.*' => 'Please provide a valid job title.',
            'email.*' => 'Please provide a valid email address.',
            'first_name.*' => 'Please provide a valid first name.',
            'last_name.*' => 'Please provide a valid  last name.',
            'registered_since.*' => 'Please provide a valid registration date.',
            'phone.*' => 'Please enter a valid phone number.',
        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with creating the order
        $requestData = $validator->validated();

        // Customer ID exists, Proceed with order creation
        $newCustomer = Customer::create($requestData);

        throw_if(!$newCustomer, GeneralJsonException::class, 'Failed to create the customer.', 422);

        return new CustomerResource($newCustomer);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return CustomerResource
     * @throws Throwable
     */
    public function show($id)
    {
        // Find the existing order
        $customer = Customer::find($id);

        throw_if(!$customer, GeneralJsonException::class, 'Customer not found!', 404);

        return new CustomerResource($customer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Customer $customer
     * @return Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return CustomerResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        // Find the existing customer
        $customer = Customer::find($request->id);

        throw_if(!$customer, GeneralJsonException::class, 'Customer not found!', 404);

        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'registered_since' => 'required|date',
            'phone' => 'required|string|max:20',
        ], [
            'job_title.*' => 'Please provide a valid job title.',
            'email.unique' => 'Email already exists, Please provide a new email address.',
            'email.*' => 'Please provide a valid email address.',
            'first_name.*' => 'Please provide a valid first name.',
            'last_name.*' => 'Please provide a valid  last name.',
            'registered_since.*' => 'Please provide a valid registration date.',
            'phone.*' => 'Please enter a valid phone number.',
        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with updating the order
        $validatedData = $validator->validated();

        $updated = $customer->update([
            'job_title' => $validatedData['job_title'],
            'email' => $validatedData['email'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'registered_since' => $validatedData['registered_since'],
            'phone' => $validatedData['phone'],
        ]);

        throw_if(!$updated, GeneralJsonException::class, 'Failed to update model.', 400);

        return new CustomerResource($customer);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return CustomerResource
     * @throws ValidationException
     * @throws Throwable
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        throw_if(!$customer, GeneralJsonException::class, 'Customer not found!', 404);

        $deleted = $customer->delete();

        throw_if(!$deleted, GeneralJsonException::class, 'Could not delete the resource.', 404);

        return throw new GeneralJsonException('Customer deleted successfully!', 200);
    }

}
