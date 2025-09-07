<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class OrderController extends Controller
{

    private function generateOrderId($length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $orderId = '';
        for ($i = 0; $i < $length; $i++) {
            $orderId .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $orderId;
    }



    public function getOrders($id)
    {
        if (empty($id)) {
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


}
