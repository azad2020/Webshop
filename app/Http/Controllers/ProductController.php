<?php

namespace App\Http\Controllers;

use App\Exceptions\GeneralJsonException;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(Request $request)
    {
        $pageSize = $request->page_size ?? 20;
        $products = Product::query()->paginate($pageSize);

        return ProductResource::collection($products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ProductResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'productname.*' => 'Please provide a valid product name!',
            'price.*' => 'Please provide a valid price!',

        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with creating the order
        $requestData = $validator->validated();

        // Product ID exists, Proceed with order creation
        $newProduct = Product::create($requestData);

        throw_if(!$newProduct, GeneralJsonException::class, 'Failed to create the product.', 422);

        return new ProductResource($newProduct);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return ProductResource
     * @throws Throwable
     */
    public function show($id)
    {
        // Find the existing order
        $product = Product::find($id);

        throw_if(!$product, GeneralJsonException::class, 'Product not found!', 404);

        return new ProductResource($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return ProductResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request): ProductResource
    {
        // Find the existing product
        $product = Product::find($request->id);

        throw_if(!$product, GeneralJsonException::class, 'Product not found!', 404);

        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'productname.*' => 'Please provide a valid product name!',
            'price.*' => 'Please provide a valid price!',
        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with updating the product
        $validatedData = $validator->validated();

        $updated = $product->update([
            'productname' => $validatedData['productname'],
            'price' => $validatedData['price'],
        ]);

        throw_if(!$updated, GeneralJsonException::class, 'Failed to update model.', 400);

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return ProductResource
     * @throws ValidationException
     * @throws Throwable
     */
    public function destroy($id): ProductResource
    {
        $product = Product::find($id);

        throw_if(!$product, GeneralJsonException::class, 'Product not found!', 404);

        $deleted = $product->delete();

        throw_if(!$deleted, GeneralJsonException::class, 'Could not delete the resource.', 404);

        return throw new GeneralJsonException('Product deleted successfully!', 200);
    }

}
