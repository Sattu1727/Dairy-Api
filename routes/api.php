<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;

// User authentication route (Requires Sanctum authentication middleware)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ----------------------------------
// Product API Routes
// ----------------------------------
Route::prefix('products')->group(function () {
    // Get all products
    Route::get('/', [ProductController::class, 'index']);

    // Get a single product by ID
    Route::get('/{id}', [ProductController::class, 'show']);

    // Create a new product
    Route::post('/', [ProductController::class, 'store']);

    // Update an existing product (Full update using PUT & Partial update using PATCH)
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::patch('/{id}', [ProductController::class, 'update']);

    // Delete a product (Using product_unique_id for soft delete)
    Route::delete('/{product_unique_id}', [ProductController::class, 'destroy']);
});

// ----------------------------------
// Product Category API Routes
// ----------------------------------
Route::prefix('product-categories')->group(function () {
    // Get all product categories
    Route::get('/', [ProductCategoryController::class, 'index']);

    // Get a single product category by ID
    Route::get('/{id}', [ProductCategoryController::class, 'show']);

    // Create a new product category
    Route::post('/', [ProductCategoryController::class, 'store']);

    // Update an existing product category
    Route::put('/{id}', [ProductCategoryController::class, 'update']);
    Route::patch('/{id}', [ProductCategoryController::class, 'update']);

    // Delete a product category
    Route::delete('/{id}', [ProductCategoryController::class, 'destroy']);
});

// ----------------------------------
// Price API Routes
// ----------------------------------
Route::prefix('prices')->group(function () {
    // Get all prices
    Route::get('/', [PriceController::class, 'index']);

    // Get a single price entry by ID
    Route::get('/{id}', [PriceController::class, 'show']);

    // Create a new price entry
    Route::post('/', [PriceController::class, 'store']);

    // Update an existing price entry
    Route::put('/{id}', [PriceController::class, 'update']);
    Route::patch('/{id}', [PriceController::class, 'update']);

    // Delete a price entry
    Route::delete('/{id}', [PriceController::class, 'destroy']);
});

Route::apiResource('stocks', StockController::class);


use App\Http\Controllers\AuthController;

Route::prefix('admin-register')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/get-alluser', [AuthController::class, 'getAllUsers']);
});


use App\Http\Controllers\AdminTypeController;

// Admin Type Routes
Route::prefix('admin-types')->group(function () {
    Route::get('/', [AdminTypeController::class, 'index']);
    Route::post('/', [AdminTypeController::class, 'store']);
    Route::get('/{id}', [AdminTypeController::class, 'show']);
    Route::put('/{id}', [AdminTypeController::class, 'update']);
    Route::delete('/{id}', [AdminTypeController::class, 'destroy']);
});


Route::prefix('user-register')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/update/{id}', [UserController::class, 'update']);
    Route::get('/get/{id}', [UserController::class, 'getUserById']);
    Route::get('/get', [UserController::class, 'getAllUsers']);
});