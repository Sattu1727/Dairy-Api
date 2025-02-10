<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    // Get all prices
    public function index()
    {
        $prices = Price::with('product')->get();
        return response()->json([
            'success' => true,
            'message' => 'Price list retrieved successfully.',
            'data' => $prices
        ], 200);
    }

    // Get a single price by ID
    public function show($id)
    {
        $price = Price::with('product')->find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price retrieved successfully.',
            'data' => $price
        ], 200);
    }

    // Create a new price
    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric',
            'status' => 'required|in:in_use,not_in_use',
            'product_id' => 'required',
            'end_date' => 'nullable|date',
        ]);

        // Check if the product_id exists in the products table
        $productExists = DB::table('products')->where('product_unique_id', $request->product_id)->exists();

        if (!$productExists) {
            return response()->json([
                'success' => false,
                'message' => 'No product found with the given product_id.',
                'data' => null
            ], 404);
        }

        // Check if a price for the same product already exists
        $existingPrice = Price::where('product_id', $request->product_id)->first();

        if ($existingPrice) {
            return response()->json([
                'success' => false,
                'message' => 'A price entry for this product already exists.',
                'data' => null
            ], 409);
        }

        // Create the price entry
        $price = Price::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Price created successfully.',
            'data' => $price
        ], 201);
    }

    // Update a price by ID
    public function update(Request $request, $id)
    {
        $price = Price::find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found.',
                'data' => null
            ], 404);
        }

        $request->validate([
            'price' => 'numeric',
            'status' => 'in:in_use,not_in_use',
            'product_id' => 'required',
            'end_date' => 'nullable|date',
        ]);

        // Check if the product_id exists in the products table
        $productExists = DB::table('products')->where('product_unique_id', $request->product_id)->exists();

        if (!$productExists) {
            return response()->json([
                'success' => false,
                'message' => 'No product found with the given product_id.',
                'data' => null
            ], 404);
        }

        $price->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully.',
            'data' => $price
        ], 200);
    }

    // Delete a price by ID
    public function destroy($id)
    {
        $price = Price::find($id);

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found.',
                'data' => null
            ], 404);
        }

        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price deleted successfully.',
            'data' => null
        ], 200);
    }
}
