<?php

namespace App\Http\Controllers;

use App\Database;
use PDO;
use PDOException;
use App\Http\Controllers\ProductController;
use App\Mailer;

use Illuminate\Http\Request;


    class OrderController extends Controller {
        private $db;
        private $products;
        private $mailer;

        private function generateOrderId($length = 8) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $orderId = '';
            for ($i = 0; $i < $length; $i++) {
                $orderId .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $orderId;
        }

        public function __construct() {
            $this->db = (new Database())->connect();
            $this->products = new ProductController();
            $this->mailer = new Mailer();
        }

        public function getOrders($id) {

            echo "yila";

        }

        public function getOrderById($orderId) {
            $query = 'SELECT * FROM orders WHERE orderId = :orderId';
            $stmt = $this->db->prepare($query);

            try {
                $stmt->bindParam(':orderId', $orderId, PDO::PARAM_STR);
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

                if ($orders) {
                    echo json_encode($orders);
                } else {
                    echo json_encode([]);
                }
            } catch (PDOException $e) {
                return [
                    "status" => "error",
                    "message" => "An error occurred: " . $e->getMessage()
                ];
            }
        }

        public function getIdsArray() {
            $query = 'SELECT orderId FROM orders';
            $stmt = $this->db->prepare($query);

            try {
                $stmt->execute();
                $IdsArray = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

                if ($IdsArray) {
                    echo json_encode($IdsArray);
                } else {
                    echo json_encode([]);
                }
            } catch (PDOException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "An error occurred: " . $e->getMessage()
                ]);
            }
        }

        public function createOrder(Request $request) {

            // Generate a unique orderId
            $orderId = $this->generateOrderId();

            $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Lagos'));
            $date = $dateTime->format('m-d-Y h:i A');
            $productJson = json_encode($request->product);

            $query = 'INSERT INTO orders (
                    orderId, name, status, deliveryFee, subtotal, total, amountPaid, balance,
                    coupon, discount, product, deliveryState, deliveryMethod, payMethod, date
                )
                VALUES (
                    :orderId, :name, :status, :deliveryFee, :subtotal, :total, :amountPaid, :balance,
                    :coupon, :discount, :product, :deliveryState, :deliveryMethod, :payMethod, :date
                )';

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':orderId', $orderId);
            $stmt->bindParam(':name', $request->name);
            $stmt->bindParam(':status', $request->status);
            $stmt->bindParam(':deliveryFee', $request->deliveryFee);
            $stmt->bindParam(':subtotal', $request->subtotal);
            $stmt->bindParam(':total', $request->total);
            $stmt->bindParam(':amountPaid', $request->amountPaid);
            $stmt->bindParam(':balance', $request->balance);
            $stmt->bindParam(':coupon', $request->coupon);
            $stmt->bindParam(':discount', $request->discount);
            $stmt->bindParam(':product', $productJson);
            $stmt->bindParam(':deliveryState', $request->deliveryState);
            $stmt->bindParam(':deliveryMethod', $request->deliveryMethod);
            $stmt->bindParam(':payMethod', $request->payMethod);
            $stmt->bindParam(':date', $date);

            if ($stmt->execute()) {
                $this->updateProductSalesCount($request->product);
                echo json_encode([
                    "status" => "success",
                    "message" => 'Order ' . $request->orderId . ' created successfully.'
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Order creation failed."
                ]);
            }
        }

        public function updateStatus(Request $request) {
            $query = 'UPDATE orders SET status = :status, updated_at = NOW() WHERE orderId = :orderId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':orderId', $request->orderId, PDO::PARAM_STR);
            $stmt->bindParam(':status', $request->status, PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "Success",
                    "message" => 'Status set to '.$request->status.' successfully'
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Order update failed."
                ]);
            }


        }

        public function payBalance(Request $request) {
            $query = 'UPDATE orders
                    SET balance = GREATEST(0, balance - :amount), amountPaid = amountPaid + :amount, updated_at = NOW(),
                    status = CASE
                                WHEN balance = 0 AND email = "" THEN "Pending"
                                WHEN balance = 0 AND email != "" THEN "Ordered"
                                ELSE status
                            END
                    WHERE orderId = :orderId';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':orderId', $request->orderId, PDO::PARAM_STR);
            $stmt->bindParam(':amount', $request->amount, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "Success",
                    "message" => 'Amount paid successfully'
                ]);
            } else {
                echo json_encode([
                    "status" => "Error",
                    "message" => "An error occured, please try again."
                ]);
            }
        }

        public function customerAction(Request $request) {
            $query = 'UPDATE orders
                      SET clientName = :clientName,
                          email = :email,
                          phone = :phone,
                          address = :address,
                          status = :newStatus,
                          updated_at = NOW()
                      WHERE orderId = :orderId';


            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':orderId', $request->orderId, PDO::PARAM_STR);
            $stmt->bindParam(':clientName', $request->clientName, PDO::PARAM_STR);
            $stmt->bindParam(':email', $request->email, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $request->phone, PDO::PARAM_STR);
            $stmt->bindParam(':address', $request->address, PDO::PARAM_STR);
            if ($request->status === "Pending") {
                $newStatus = "Ordered";
            } else if ($request->status === "Installment") {
                $newStatus = "Installment";
            }
            $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // $this->mailer->mailIt($request->orderId);
                echo json_encode([
                    "status" => "Success",
                    "message" => 'Thanks! order created successfully'
                ]);
            } else {
                echo json_encode([
                    "status" => "Error",
                    "message" => "An error occured, please try again."
                ]);
            }
        }

        public function updateProductSalesCount($productArray) {
            foreach ($productArray as $product) {
                $name = $product->name;
                $productCount = $product->quantity;

                $this->products->updateSalesCount($name, $productCount);
            }
        }

    }
?>
