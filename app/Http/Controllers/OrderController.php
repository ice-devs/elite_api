<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\ProductController;



class OrderController extends Controller
{

    private $products;

    // public function __construct()
    // {
    //     $this->products = new ProductController();
    // }

    // private function generateOrderId($length = 8)
    // {
    //     $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    //     $orderId = '';
    //     for ($i = 0; $i < $length; $i++) {
    //         $orderId .= $characters[rand(0, strlen($characters) - 1)];
    //     }
    //     return $orderId;
    // }

    public function getOrders($id = null)
    {
        if (is_null($id)) {
            // Get all orders, latest first
            $orders = DB::table('orders')
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json($orders);
        } elseif ($id === 'idsArray') {
            return $this->getIdsArray();
        } else {
            return $this->getOrderById($id);
        }
    }

    public function getOrderById($orderId)
    {
        $order = DB::table('orders')
            ->where('orderId', $orderId)
            ->first(); // single row

        if ($order) {
            return response()->json($order);
        }

        return response()->json([]);
    }

    public function getIdsArray()
    {
        $ids = DB::table('orders')
            ->pluck('orderId'); // fetch just orderId column

        return response()->json($ids);
    }

    public function createOrder(Request $request)
    {
        // Generate a unique orderId
        $orderId = Str::uuid();

        $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Lagos'));
        $date = $dateTime->format('m-d-Y h:i A');

        // Convert product to JSON
        $productJson = json_encode($request->input('product'));

        try {
            // Insert order into DB
            DB::table('orders')->insert([
                'orderId'        => $orderId,
                'clientName'     => $request->input('clientName', ''), // fallback if not provided
                'email'          => $request->input('email', ''),
                'phone'          => $request->input('phone', ''),
                'address'        => $request->input('address', ''),
                'name'           => $request->input('name'),
                'status'         => $request->input('status'),
                'deliveryFee'    => $request->input('deliveryFee'),
                'subtotal'       => $request->input('subtotal'),
                'total'          => $request->input('total'),
                'amountPaid'     => $request->input('amountPaid'),
                'balance'        => $request->input('balance'),
                'coupon'         => $request->input('coupon'),
                'discount'       => $request->input('discount'),
                'product'        => $productJson,
                'deliveryState'  => $request->input('deliveryState'),
                'deliveryMethod' => $request->input('deliveryMethod'),
                'payMethod'      => $request->input('payMethod'),
                'date'           => $date,
            ]);

            // Update product sales count
            $this->updateProductSalesCount($productJson);

            return response()->json([
                "status"  => "success",
                "message" => "Order {$orderId} created successfully."
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "status"  => "error",
                "message" => "Order creation failed.",
                "error"   => $e->getMessage()
            ], 500);
        }
    }

    public function updateProductSalesCount($productArray)
    {
        foreach ($productArray as $product) {
            $productId = $product->productId;
            $productCount = $product->quantity;

            ProductController::updateSalesCount($productId, $productCount);
        }
    }


}



