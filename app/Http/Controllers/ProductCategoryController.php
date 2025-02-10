<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    // Get all product categories
    public function index()
    {
        $categories = ProductCategory::all();

        if ($categories->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No categories found',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product categories retrieved successfully',
            'data' => $categories
        ]);
    }

    // Get a single product category by ID
    public function show($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found',
                'data' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product category retrieved successfully',
            'data' => $category
        ]);
    }

    // Create a new product category
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string|max:1000',
        ]);

        // Check if the category name already exists
        if (ProductCategory::where('name', $validated['name'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product category with this name already exists',
                'data' => null
            ], 400);
        }

        // Create category
        $category = ProductCategory::create([
            'name' => trim($validated['name']),
            'desc' => trim($validated['desc'] ?? ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product category created successfully',
            'data' => $category
        ], 201);
    }


    // Update an existing product category
    public function update(Request $request, $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found',
                'data' => false
            ], 404);
        }

        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'desc' => 'nullable|string|max:1000',
        ]);

        // Update category
        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product category updated successfully',
            'data' => $category
        ]);
    }

    // Delete a product category (soft delete)
    public function destroy($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Product category not found',
                'data' => false
            ], 404);
        }

        // Soft delete
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product category deleted successfully',
            'data' => true
        ]);
    }
}
