<?php

namespace App\Http\Controllers;


use App\Exceptions\GeneralJsonException;
use App\Http\Resources\OrderResource;
use App\Interfaces\PaymentProviderInterface;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{

    private $paymentProvider;

    public function __construct(PaymentProviderInterface $paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
    }
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(Request $request)
    {
        $pageSize = $request->page_size ?? 20;
        $orders = Order::query()->paginate($pageSize);

        return OrderResource::collection($orders);
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
     * @return OrderResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'paid' => 'required|boolean',
        ], [
            'customer_id.required' => 'Please enter a value for customer_id.',
            'customer_id.exists' => 'Provided customer_id does not exist in customers table.',
            'paid.required' => 'Please enter a value for paid',
            'paid.boolean' => 'paid must be boolean.',
        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with creating the order
        $requestData = $validator->validated();

        // Customer ID exists, Proceed with order creation
        $newOrder = Order::create($requestData);

        throw_if(!$newOrder, GeneralJsonException::class, 'Failed to create the order.', 422);

        return new OrderResource($newOrder);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return OrderResource
     * @throws Throwable
     */
    public function show($id)
    {
        // Find the existing order
        $order = Order::find($id);

        throw_if(!$order, GeneralJsonException::class, 'Order not found!', 404);

        return new OrderResource($order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Order $order
     * @return OrderResource
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request): OrderResource
    {
        // Find the existing order
        $order = Order::find($request->id);

        throw_if(!$order, GeneralJsonException::class, 'Order not found!', 404);

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'paid' => 'required|boolean',
        ], [
            'customer_id.required' => 'Please enter a value for customer_id.',
            'customer_id.integer' => 'customer_id must be an integer.',
            'customer_id.exists' => 'Provided customer_id does not exist in customers table.',
            'paid.required' => 'Please enter a value for paid',
            'paid.boolean' => 'paid must be boolean.',
        ]);

        // Check for validation errors
        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation passed, proceed with updating the order
        $validatedData = $validator->validated();

        $updated = $order->update([
            'customer_id' => $validatedData['customer_id'],
            'paid' => $validatedData['paid'],
        ]);

        throw_if(!$updated, GeneralJsonException::class, 'Failed to update model.', 400);

        return new OrderResource($order);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return OrderResource
     * @throws ValidationException
     * @throws Throwable
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        throw_if(!$order, GeneralJsonException::class, 'Order not found!', 404);

        $deleted = $order->delete();

        throw_if(!$deleted, GeneralJsonException::class, 'Could not delete the resource.', 404);

        return throw new GeneralJsonException('Order deleted successfully!', 200);
    }


    /**
     * @throws Throwable
     */
    public function addProduct(Request $request, Order $order)
    {
        // Check if the order is already paid
        throw_if($order->paid, GeneralJsonException::class, 'Cannot add product to a paid order. Order has already been paid.', 400);

        // Get the product ID from the request
        $productId = $request->input('product_id');

        // Find the product
        $product = Product::find($productId);

        throw_if(!$product, GeneralJsonException::class, 'Product not found. Please provide a valid product ID.', 404);

        // Add the product to the order
        $order->products()->attach($productId);

        return throw new GeneralJsonException('Product added to the order successfully.', 200);
    }


    /**
     * @throws Throwable
     */
    public function payOrder(Request $request, $id)
    {
        // Retrieve payment details from the request
        $orderId = $request->input('order_id');
        $customerEmail = $request->input('customer_email');
        $value = $request->input('value');

        $order = Order::find($id);

        // Check if order exists
        throw_if(!$order, GeneralJsonException::class, 'Order not found!', 404);

        // Validate if the provided order ID matches the requested order
        throw_if($id != $orderId, GeneralJsonException::class, 'Order ID mismatch.', 400);

        // Check if the order is already paid
        throw_if($order->paid, GeneralJsonException::class, 'Order is already paid.', 400);

        // Prepare the payment request payload
        $paymentData = [
            'order_id' => $orderId,
            'customer_email' => $customerEmail,
            'value' => $value
        ];

        // Make a POST request to the payment provider endpoint
        $paymentResponse = $this->paymentProvider->processPayment($paymentData);

        // Make a POST request to the "Super Payment Provider" endpoint
        //$paymentResponse = Http::post('https://superpay.view.agentur-loop.com/pay', $paymentData);

        // Handle payment response
        if ($paymentResponse->successful())
        {
            // Successful payment scenario (status code 2xx)
            $order = Order::findOrFail($id);
            $order->paid = true;
            $order->save();

            return throw new GeneralJsonException('Payment Successful.', 200);
        }
        elseif ($paymentResponse->clientError())
        {
            // Client error scenario (status code 4xx)
            if ($paymentResponse->status() === 402)
            {
                // Insufficient funds scenario
                return throw new GeneralJsonException('Insufficient Funds!', 402);
            }
            else
            {
                // Other client errors
                return throw new GeneralJsonException('Client Error!', $paymentResponse->status());
            }
        }
        elseif ($paymentResponse->serverError())
        {
            // Server error scenario (status code 5xx)
            return throw new GeneralJsonException('Server Error!', $paymentResponse->status());
        }
        else
        {
            // Any Other scenarios (uncommon)
            return throw new GeneralJsonException('Unexpected Error!', $paymentResponse->status());
        }

    }

}
