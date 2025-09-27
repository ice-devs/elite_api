<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\CloudinaryUploader;

class ProductController extends Controller
{

    private function saveFile($file)
    {
        $fileUrl = CloudinaryUploader::uploadFile($file);
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


    public static function updateSalesCount($productId, $salesCount)
    {
        $updated = DB::table('products')
            ->where('productId', $productId)
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
        $productId = Str::uuid();


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

    public function updateProduct(Request $request)
    {
        // Handle file uploads (replace with Laravel storage if needed)
        $image1 = $request->file('image1') ? $this->saveFile($request->file('image1')) : '';
        $image2 = $request->file('image2') ? $this->saveFile($request->file('image2')) : '';
        $image3 = $request->file('image3') ? $this->saveFile($request->file('image3')) : '';
        $image4 = $request->file('image4') ? $this->saveFile($request->file('image4')) : '';

        $updated = DB::table('products')
            ->where('productId', $request->input('productId') )
            ->update([
                'name'        => $request->input('name'),
                'description' => $request->input('description'),
                'quantity'    => $request->input('quantity'),
                'price'       => $request->input('price'),
                'category'    => $request->input('category'),
                'status'      => $request->input('status'),
                'image1'      => $image1,
                'image2'      => $image2,
                'image3'      => $image3,
                'image4'      => $image4,
                'updated_at'  => now(),
            ]);

        if ($updated) {
            return response()->json([
                "status" => "success",
                "message" => "Product updated successfully"
            ]);
        }

        return response()->json([
            "status" => "error",
            "message" => "Product update failed."
        ], 500);
    }

    public function updateStock(Request $request)
    {
        $productId = $request->input('productId'); // or $request->route('id') if in route
        $quantity  = $request->input('quantity');
        $type      = $request->input('type');

        if ($type === "removed") {
            $updated = DB::table('products')
                ->where('productId', $productId)
                ->update([
                    'quantity'   => DB::raw("GREATEST(0, quantity - $quantity)"),
                    'updated_at' => now(),
                ]);
        } elseif ($type === "added") {
            $updated = DB::table('products')
                ->where('productId', $productId)
                ->update([
                    'quantity'   => DB::raw("quantity + $quantity"),
                    'updated_at' => now(),
                ]);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Invalid stock update type"
            ], 400);
        }

        if ($updated) {
            return response()->json([
                "status" => "success",
                "message" => "$quantity products $type successfully"
            ]);
        }

        return response()->json([
            "status" => "error",
            "message" => "Stock update failed."
        ], 500);
    }

    public function updateStatus(Request $request)
    {
        $productId = $request->input('productId');
        $status    = $request->input('status');

        $updated = DB::table('products')
            ->where('productId', $productId)
            ->update([
                'status'     => $status,
                'updated_at' => now(),
            ]);

        if ($updated) {
            return response()->json([
                "status" => "success",
                "message" => "Product status set to $status successfully"
            ]);
        }

        return response()->json([
            "status" => "error",
            "message" => "Status update failed."
        ], 500);
    }
}

