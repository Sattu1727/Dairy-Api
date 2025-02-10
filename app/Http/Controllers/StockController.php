<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::where('is_deleted', false)->get();

        return response()->json([
            'success' => true,
            'message' => 'Stock list retrieved successfully',
            'data' => $stocks->map(function ($stock) {
                return [
                    'product_id' => $stock->product_id,
                    'stock_id' => $stock->stock_id,
                    'quantity_in' => $stock->quantity_in,
                    'quantity_out' => $stock->quantity_out,
                    'current_stock' => $stock->current_stock, // Now directly retrieved from DB
                    'stock_status' => $stock->stock_status,
                    'stock_threshold' => $stock->stock_threshold,
                    'last_sold_at' => $stock->last_sold_at,
                    'batch_number' => $stock->batch_number,
                    'status' => $stock->status
                ];
            })
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|uuid|exists:products,product_unique_id',
            'stock_id' => 'required|string|unique:stocks,stock_id',
            'quantity_in' => 'required|integer|min:1',
            'stock_threshold' => 'nullable|integer|min:0',
            'last_sold_at' => 'nullable|date',
            'batch_number' => 'nullable|string',
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stockData = $request->all();
            $stockData['current_stock'] = $stockData['quantity_in'];
            $stockData['stock_status'] = 'full_stock';

            if (!empty($request->last_sold_at)) {
                $stockData['last_sold_at'] = Carbon::parse($request->last_sold_at)->format('Y-m-d H:i:s');
            }

            $stock = Stock::create($stockData);
            return response()->json([
                'success' => true,
                'message' => 'Stock created successfully',
                'data' => $stock
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = Stock::where('id', $id)->where('is_deleted', false)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'quantity_out' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newQuantityOut = $request->quantity_out ?? 0;
            $currentStock = $stock->current_stock - $newQuantityOut; // Subtract from last current stock

            if ($currentStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Available stock: ' . $stock->current_stock,
                ], 400);
            }

            $stock->update([
                'quantity_out' => $stock->quantity_out + $newQuantityOut,
                'current_stock' => $currentStock,
            ]);

            $this->updateStockStatus($stock);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $stock
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found',
            ], 404);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function updateStockStatus(Stock $stock)
    {
        if ($stock->current_stock == 0) {
            $stock->update(['stock_status' => 'out_of_stock']);
        } elseif ($stock->current_stock <= $stock->stock_threshold) {
            $stock->update(['stock_status' => 'low_stock']);
        } else {
            $stock->update(['stock_status' => 'full_stock']);
        }
    }

    public function destroy($id)
    {
        try {
            $stock = Stock::where('id', $id)->where('is_deleted', false)->firstOrFail();
            $stock->update(['is_deleted' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Stock marked as deleted',
                'data' => $stock
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found',
            ], 404);
        }
    }
}
