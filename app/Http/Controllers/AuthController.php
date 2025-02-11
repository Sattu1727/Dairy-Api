<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Registration
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:admin_users',
            'password' => 'required|min:6',
            'first_name' => 'required',
            'last_name' => 'required',
            'type_id' => 'required|exists:admin_types,id',
        ]);

        try {
            $user = AdminUser::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'type_id' => $request->type_id,
                'status' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'created_at' => $user->created_at,
                    'admin_type' => $user->adminType->admin_type,
                    'permission' => $user->adminType->permission,
                    'role' => $user->adminType->admin_type,
                    'token' => $token
                ]
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }
    // Get all registered users with name, role, and date
    public function getAllUsers()
    {
        $users = AdminUser::with('adminType:id,admin_type')->select('first_name', 'last_name', 'type_id', 'created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'User list retrieved successfully.',
            'data' => $users
        ], 200);
    }
}
