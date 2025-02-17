<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Registration with Username, Email, and Mobile
    public function register(Request $request)
    {
        $request->validate([
            'username'   => 'required|unique:admin_users',
            'email'      => 'required|unique:admin_users',
            'mobile'     => 'required|unique:admin_users|digits:10',
            'password'   => 'required|min:6',
            'first_name' => 'required',
            'last_name'  => 'required',
            'type_id'    => 'required|exists:admin_types,id',
        ]);

        try {
            $user = AdminUser::create([
                'username'   => $request->username,
                'email'      => $request->email,
                'mobile'     => $request->mobile,
                'password'   => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'type_id'    => $request->type_id,
                'status'     => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'data'    => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Login with Username, Email, or Mobile
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required',
            'password' => 'required',
        ]);

        // Check if the login input is email, mobile, or username
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($request->login) ? 'mobile' : 'username');

        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            $user  = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data'    => [
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'mobile'     => $user->mobile,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'created_at' => $user->created_at,
                    'admin_type' => $user->adminType->admin_type,
                    'permission' => $user->adminType->permission,
                    'role'       => $user->adminType->admin_type,
                    'token'      => $token
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }

    // Get all registered users
    public function getAllUsers()
    {
        $users = AdminUser::with('adminType:id,admin_type')
            ->select('first_name', 'last_name', 'email', 'mobile', 'type_id', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User list retrieved successfully.',
            'data'    => $users
        ], 200);
    }
}
