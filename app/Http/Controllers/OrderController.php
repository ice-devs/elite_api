<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\ProductController;
use App\Mailer;



class OrderController extends Controller
{


    private $Mailer;
    public function __construct()
    {
        // Initialize Mailer class
        $this->Mailer = new Mailer;

    }

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

    public static function getOrderById($orderId)
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
                'clientName'     => $request->input('clientName'),
                'email'          => $request->input('email'),
                'phone'          => $request->input('phone'),
                'address'        => $request->input('address'),
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
                'date'           => $date
            ]);

            // $sendMail = $this->Mailer->mailIt($request, $orderId);
            // $sendMail;


            // if (!$sendMail) {
            //     return response()->json([
            //         "status" => "error",
            //         "message" => "Mail not sent"
            //     ], 400);
            // }


            // Update product sales count
            $productArray = json_decode($request->input('product'), true);

            if (!is_array($productArray)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Invalid product data format"
                ], 400);
            }

            $this->updateProductSalesCount($productArray);

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

    public function updateProductSalesCount(array $productArray)
    {
        foreach ($productArray as $product) {
            $productId = $product['productId'];
            $productCount = $product['quantity'];

            ProductController::updateSalesCount($productId, $productCount);
        }
    }

    public function updateStatus(Request $request)
    {
        $updated = DB::table('orders')
            ->where('orderId', $request->orderId)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        if ($updated) {
            return response()->json([
                "status" => "Success",
                "message" => "Status set to {$request->status} successfully"
            ]);
        }

        return response()->json([
            "status" => "Error",
            "message" => "Order update failed."
        ], 400);
    }

    public function payBalance(Request $request)
    {
        $updated = DB::table('orders')
            ->where('orderId', $request->orderId)
            ->update([
                'balance'   => DB::raw("GREATEST(0, balance - {$request->amount})"),
                'amountPaid'=> DB::raw("amountPaid + {$request->amount}"),
                'status'    => DB::raw("
                    CASE
                        WHEN balance = 0 AND email = '' THEN 'Pending'
                        WHEN balance = 0 AND email != '' THEN 'Ordered'
                        ELSE status
                    END
                "),
                'updated_at'=> now()
            ]);

        if ($updated) {
            return response()->json([
                "status" => "Success",
                "message" => "Amount paid successfully"
            ]);
        }

        return response()->json([
            "status" => "Error",
            "message" => "An error occurred, please try again."
        ], 400);
    }

    public function customerAction(Request $request)
    {
        $newStatus = $request->status === "Pending"
                        ? "Ordered"
                        : ($request->status === "Installment" ? "Installment" : $request->status);

        $updated = DB::table('orders')
            ->where('orderId', $request->orderId)
            ->update([
                'clientName' => $request->clientName,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'status'     => $newStatus,
                'updated_at' => now()
            ]);

        if ($updated) {
            // If you want to trigger email here later
            // $this->mailer->mailIt($request->orderId);

            return response()->json([
                "status" => "Success",
                "message" => "Thanks! order updated successfully"
            ]);
        }

        return response()->json([
            "status" => "Error",
            "message" => "An error occurred, please try again."
        ], 400);
    }


}






