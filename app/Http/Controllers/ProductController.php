<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// class ProductController extends Controller
// {
//     //
// }


// namespace App;

use App\Database;
use PDO;
use PDOException;
use App\CloudinaryUploader;

class ProductController {
    private $db;
    private $uploader;


    public function __construct() {
        $this->db = (new Database())->connect();
        $this->uploader = new CloudinaryUploader();
    }

    private function generateProductId($length = 8) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $productId = '';
        for ($i = 0; $i < $length; $i++) {
            $productId .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $productId;
    }

    private function saveFile($file) {
        $fileUrl = $this->uploader->uploadFile($file);
        return $fileUrl;
    }

    public function createProduct($name, $description, $quantity,  $price, $category, $image1, $image2, $image3, $image4) {
        $productId = $this->generateProductId();

        $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Lagos'));
        $createdAt = $dateTime->format('m-d-Y h:i A');

        // Handle file uploads
        $image1 = isset($_FILES['image1']) ? $this->saveFile($_FILES['image1']) : '';
        $image2 = isset($_FILES['image2']) ? $this->saveFile($_FILES['image2']) : '';
        $image3 = isset($_FILES['image3']) ? $this->saveFile($_FILES['image3']) : '';
        $image4 = isset($_FILES['image4']) ? $this->saveFile($_FILES['image4']) : '';

        $query = 'INSERT INTO products (
                    productId, name, description, quantity, price, category, image1, image2, image3, image4, createdAt
                  )
                  VALUES (
                    :productId, :name, :description, :quantity, :price, :category, :image1, :image2, :image3, :image4, :createdAt
                  )';

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':productId', $productId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':image2', $image2);
        $stmt->bindParam(':image3', $image3);
        $stmt->bindParam(':image4', $image4);
        $stmt->bindParam(':createdAt', $createdAt);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => 'Product ' . $productId . ' created successfully.'
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Product creation failed."
            ]);
        }
    }

    public function getProducts() {
        $query = 'SELECT * FROM products ORDER BY updated_at DESC';
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($products) {
                echo json_encode($products);
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

    public function updateProduct($productId, $name, $description, $quantity, $price, $category, $status, $image1, $image2, $image3, $image4) {

        // Handle file uploads
        $image1 = isset($_FILES['image1']) ? $this->saveFile($_FILES['image1']) : '';
        $image2 = isset($_FILES['image2']) ? $this->saveFile($_FILES['image2']) : '';
        $image3 = isset($_FILES['image3']) ? $this->saveFile($_FILES['image3']) : '';
        $image4 = isset($_FILES['image4']) ? $this->saveFile($_FILES['image4']) : '';

        $query = 'UPDATE products
                  SET name = :name, description = :description, quantity = :quantity, price = :price, category = :category, status = :status,
                      image1 = :image1, image2 = :image2, image3 = :image3, image4 = :image4, updated_at = NOW()
                  WHERE productId = :productId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price, PDO::PARAM_INT);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':image1', $image1, PDO::PARAM_STR);
        $stmt->bindParam(':image2', $image2, PDO::PARAM_STR);
        $stmt->bindParam(':image3', $image3, PDO::PARAM_STR);
        $stmt->bindParam(':image4', $image4, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => 'Product updated successfully'
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Product update failed."
            ]);
        }
    }

    public function updateSalesCount($name, $salesCount) {

        $query = 'UPDATE products
                  SET quantity = GREATEST(0, quantity - :salesCount), salesCount = salesCount + :salesCount, updated_at = NOW()
                  WHERE name = :name';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':salesCount', $salesCount, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Sales count update failed."
            ]);
        }
    }

    public function updateStock($productId, $quantity, $type) {
        if ($type === "removed") {
            $query = 'UPDATE products SET quantity = GREATEST(0, quantity - :quantity), updated_at = NOW() WHERE productId = :productId';
        } elseif ($type === "added") {
            $query = 'UPDATE products SET quantity = quantity + :quantity, updated_at = NOW() WHERE productId = :productId';
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "Success",
                "message" => $quantity. ' products '.$type. ' successfully'
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Order update failed."
            ]);
        }
    }

    public function updateStatus($productId, $status) {
        $query = 'UPDATE products SET status = :status, updated_at = NOW() WHERE productId = :productId';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':productId', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "Success",
                "message" => 'Product status set to ' .$status. ' successfully'
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Order update failed."
            ]);
        }
    }
}
?>
