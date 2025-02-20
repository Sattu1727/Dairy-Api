<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username'   => 'required|unique:admin_users',
            'email'      => 'required|email|unique:admin_users',
            'mobile'     => 'required|digits:10|unique:admin_users',
            'password'   => 'required|min:6',
            'first_name' => 'required',
            'last_name'  => 'required',
            'type_id'    => 'required|exists:admin_types,id',
            'image'      => 'nullable|image|mimes:jpeg,webp,png,jpg,gif|max:2048',
        ]);

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('uploads/users', 'public');
            }

            $user = AdminUser::create([
                'username'   => $request->username,
                'email'      => $request->email,
                'mobile'     => $request->mobile,
                'password'   => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'type_id'    => $request->type_id,
                'status'     => true,
                'image'      => $imagePath,
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

    public function updateadminuser(Request $request, $username)
    {
        $user = AdminUser::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $request->validate([
            'email'      => 'sometimes|required|email|unique:admin_users,email,' . $user->id,
            'mobile'     => 'sometimes|required|digits:10|unique:admin_users,mobile,' . $user->id,
            'password'   => 'sometimes|required|min:6',
            'first_name' => 'sometimes|required',
            'last_name'  => 'sometimes|required',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Delete the old image if exists
                if ($user->image) {
                    Storage::disk('public')->delete($user->image);
                }
                // Store new image
                $user->image = $request->file('image')->store('uploads/users', 'public');
            }

            $user->update([
                'email'      => $request->email ?? $user->email,
                'mobile'     => $request->mobile ?? $user->mobile,
                'password'   => $request->has('password') ? Hash::make($request->password) : $user->password,
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name'  => $request->last_name ?? $user->last_name,
                'image'      => $user->image, // Ensure image is not removed if not updated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User update failed.',
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
                    'image'      => $user->image ? asset('storage/' . $user->image) : null, // Include the image URL
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
            ->where('status', true)
            ->select('first_name', 'last_name', 'email', 'mobile', 'type_id', 'created_at','image')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User list retrieved successfully.',
            'data'    => $users
        ], 200);
    }
    public function updateStatus(Request $request, $username)
    {
        $user = AdminUser::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $request->validate([
            'status' => 'required|boolean',
        ]);

        try {
            $user->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User status update failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
