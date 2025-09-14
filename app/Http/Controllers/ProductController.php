<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use App\CloudinaryUploader;

class ProductController extends Controller
{
    private $uploader;


    // public function __construct()
    // {
    //     $this->uploader = new CloudinaryUploader();
    // }

    private function generateProductId($length = 8)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $productId = '';
        for ($i = 0; $i < $length; $i++) {
            $productId .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $productId;
    }

    private function saveFile($file)
    {
        // $fileUrl = $this->uploader->uploadFile($file);
        $fileUrl = "JJJ";
        return $fileUrl;
    }


    public function getProducts($id = null)
    {
        try {
            if (is_null($id)) {
                $products = DB::table('products')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                if ($products->isNotEmpty()) {
                    return response()->json($products);
                } else {
                    return response()->json([]);
                }
            } else {
                return $this->getProductsById($id);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "An error occurred: " . $e->getMessage()
            ], 500);
        }
    }


    public function getProductsById($productId)
    {
        $product = DB::table('products')
            ->where('productId', $productId)
            ->first(); // single row

        if ($product) {
            return response()->json($product);
        }

        return response()->json([]);
    }


    public function updateSalesCount($name, $salesCount)
    {
        $updated = DB::table('products')
            ->where('name', $name)
            ->update([
                'quantity' => DB::raw("GREATEST(0, quantity - $salesCount)"),
                'salesCount' => DB::raw("salesCount + $salesCount"),
                'updated_at' => now()
            ]);

        if ($updated) {
            return true;
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Sales count update failed."
            ]);
        }
    }

    public function createProduct(Request $request)
    {
        $productId = $this->generateProductId();

        // Format current time in Africa/Lagos timezone
        $dateTime = new \DateTime('now', new \DateTimeZone('Africa/Lagos'));
        $createdAt = $dateTime->format('m-d-Y h:i A');

        // Handle file uploads
        $image1 = $request->hasFile('image1') ? $this->saveFile($request->file('image1')) : '';
        $image2 = $request->hasFile('image2') ? $this->saveFile($request->file('image2')) : '';
        $image3 = $request->hasFile('image3') ? $this->saveFile($request->file('image3')) : '';
        $image4 = $request->hasFile('image4') ? $this->saveFile($request->file('image4')) : '';

        try {
            DB::table('products')->insert([
                'productId'   => $productId,
                'name'        => $request->input('name'),
                'description' => $request->input('description'),
                'quantity'    => $request->input('quantity'),
                'price'       => $request->input('price'),
                'category'    => $request->input('category'),
                'image1'      => $image1,
                'image2'      => $image2,
                'image3'      => $image3,
                'image4'      => $image4,
                'createdAt'   => $createdAt,
            ]);

            return response()->json([
                "status"  => "success",
                "message" => "Product {$productId} created successfully."
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "status"  => "error",
                "message" => "Product creation failed.",
                "error"   => $e->getMessage()
            ], 500);
        }
    }
}



