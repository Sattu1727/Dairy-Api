<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    // Get all products
    public function index()
    {
        $products = Product::where('is_deleted', false)->get();
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    // Get a single product
    public function show($id)
    {
        $product = Product::where('product_id', $id)->where('is_deleted', false)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product
        ], 200);
    }

    // Create a new product
    // Create a new product
    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'SKU' => 'required|string|max:100|unique:products,SKU',
                'category' => 'required|integer',
                'price_id' => 'nullable|integer',
                'discount_id' => 'nullable|integer|exists:discounts,id',
                'meta' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);

            // Check if category is numeric (ID) or string (name)
            $category = null;
            if (is_numeric($validated['category'])) {
                // If it's numeric, fetch the category by ID
                $category = \App\Models\ProductCategory::find($validated['category']);
            }

            // If the category is not found, return an error response
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                    'data' => null
                ], 404);
            }

            // Create the new product
            $product = Product::create([
                'product_id' => Str::uuid(),
                'product_name' => trim($validated['product_name']),
                'description' => trim($validated['description'] ?? ''),
                'SKU' => trim($validated['SKU']),
                'category_id' => $category->id, // Use the fetched category_id
                'price_id' => $validated['price_id'] ?? null,
                'discount_id' => $validated['discount_id'] ?? null,
                'meta' => $validated['meta'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }


    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::where('product_id', $id)->where('is_deleted', false)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }

        try {
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'SKU' => 'sometimes|string|max:100|unique:products,SKU,' . $product->id,
                'category_id' => 'required|integer|exists:categories,id',
                'price_id' => 'nullable|integer|exists:prices,id',
                'discount_id' => 'nullable|integer|exists:discounts,id',
                'meta' => 'nullable|integer',
                'status' => 'nullable|in:active,inactive',
            ]);

            $product->update([
                'product_name' => trim($validated['product_name']),
                'description' => trim($validated['description'] ?? ''),
                'SKU' => trim($validated['SKU'] ?? $product->SKU),
                'category_id' => $validated['category_id'],
                'price_id' => $validated['price_id'] ?? $product->price_id,
                'discount_id' => $validated['discount_id'] ?? $product->discount_id,
                'meta' => $validated['meta'] ?? $product->meta,
                'status' => $validated['status'] ?? $product->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    // Soft delete a product
    public function destroy($product_unique_id)
    {
        // Ensure the ID is a valid UUID
        if (!\Illuminate\Support\Str::isUuid($product_unique_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID format',
                'data' => null
            ], 400);
        }

        $product = Product::where('product_unique_id', $product_unique_id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }

        if ($product->is_deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already deleted',
                'data' => null
            ], 400);
        }

        $product->update(['is_deleted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'data' => null
        ], 200);
    }
}
