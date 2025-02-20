<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductImageController extends Controller
{
    // Add a new product image
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_unique_id' => 'required|exists:products,product_unique_id',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:featured,gallery',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Check if the product exists
        $product = Product::where('product_unique_id', $request->product_unique_id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product with the given ID does not exist'], 404);
        }

        // Handle file upload
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
        } else {
            return response()->json(['error' => 'No image uploaded'], 400);
        }

        // Create the product image record
        try {
            $productImage = ProductImage::create([
                'product_unique_id' => $product->product_unique_id,
                'product_image' => $imagePath,
                'type' => $request->type,
                'status' => $request->status ?? 'active',
            ]);

            return response()->json(['message' => 'Product image added successfully', 'data' => $productImage], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while saving the image', 'details' => $e->getMessage()], 500);
        }
    }

    // Get all product images
    public function index()
    {
        try {
            $productImages = ProductImage::where('is_deleted', false)->get();
            return response()->json(['data' => $productImages], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve product images', 'details' => $e->getMessage()], 500);
        }
    }

    // Soft delete a product image
    public function destroy($id)
    {
        $productImage = ProductImage::find($id);

        if (!$productImage) {
            return response()->json(['error' => 'Product image not found'], 404);
        }

        try {
            $productImage->update(['is_deleted' => true]);
            return response()->json(['message' => 'Product image deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the image', 'details' => $e->getMessage()], 500);
        }
    }
    // Get all product images for a specific product by product unique ID
    public function show($product_unique_id)
    {
        // Validate that the product exists
        $product = Product::where('product_unique_id', $product_unique_id)->first();

        if (!$product) {
            return response()->json(['error' => 'Product with the given ID does not exist'], 404);
        }

        // Retrieve all product images related to this product, excluding deleted ones
        $productImages = ProductImage::where('product_unique_id', $product_unique_id)
            ->where('is_deleted', false)
            ->get();

        // Check if any images exist for the product
        if ($productImages->isEmpty()) {
            return response()->json(['message' => 'No images found for this product'], 404);
        }

        return response()->json(['data' => $productImages], 200);
    }
}