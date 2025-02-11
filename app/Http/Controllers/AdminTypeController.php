<?php

// app/Http/Controllers/AdminTypeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminType;
use Illuminate\Support\Facades\Validator;

class AdminTypeController extends Controller
{
    // Get all admin types
    public function index()
    {
        $adminTypes = AdminType::all();
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Admin types retrieved successfully',
            'data' => $adminTypes
        ], 200);
    }

    // Create a new admin type
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_type' => 'required|string|unique:admin_types|in:Super Admin,Admin,Manager,Staff',
            'permission' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        $adminType = AdminType::create($request->only(['admin_type', 'permission']));
        return response()->json([
            'success' => true,
            'status' => 201,
            'message' => 'Admin type created successfully',
            'data' => $adminType
        ], 201);
    }

    // Get a specific admin type by ID
    public function show($id)
    {
        $adminType = AdminType::find($id);
        if (!$adminType) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Admin type not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Admin type retrieved successfully',
            'data' => $adminType
        ], 200);
    }

    // Update an admin type
    public function update(Request $request, $id)
    {
        $adminType = AdminType::find($id);
        if (!$adminType) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Admin type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'admin_type' => 'sometimes|string|unique:admin_types,admin_type,' . $id . '|in:Super Admin,Admin,Manager,Staff',
            'permission' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        $adminType->update($request->only(['admin_type', 'permission']));
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Admin type updated successfully',
            'data' => $adminType
        ], 200);
    }

    // Delete an admin type
    public function destroy($id)
    {
        $adminType = AdminType::find($id);
        if (!$adminType) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'Admin type not found'
            ], 404);
        }

        $adminType->delete();
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Admin type deleted successfully'
        ], 200);
    }
}
