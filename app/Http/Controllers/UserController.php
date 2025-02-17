<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/u' // Ensures name contains only letters & spaces
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Ensures valid email format
                'max:255',
                'unique:users'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/', // Ensures exactly 10 digits
                'unique:users'
            ],
            'password' => 'required|string|min:6',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $filename = $originalName . '-' . date('d-m-y') . '.' . $extension;

            // Store the image in "public/avatars"
            $image->storeAs('avatars', $filename, 'public');
            $avatarPath = $filename; // Save only filename in DB
        }

        // Create user
        $user = User::create([
            'Userid' => strtolower(Str::slug($request->name)) . '@' . Str::random(6),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath,
            'status' => 'Active',
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/u' // Ensures name contains only letters & spaces
            ],
            'email' => [
                'sometimes',
                'string',
                'email:rfc,dns', // Ensures valid email format
                'max:255',
                'unique:users,email,' . $id
            ],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^[0-9]{10}$/', // Ensures exactly 10 digits
                'unique:users,phone,' . $id
            ],
            'password' => 'sometimes|string|min:6',
            'bio' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar); // Delete old avatar
            }

            $image = $request->file('avatar');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();

            // Generate new filename: original-name + current date
            $filename = $originalName . '-' . date('d-m-y') . '.' . $extension;

            // Store the file in "public/avatars"
            $image->storeAs('avatars', $filename, 'public');

            // Save only the new filename in the database
            $user->avatar = $filename;
        }

        // Update other fields
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->input('name', $user->name);
        $user->email = $request->input('email', $user->email);
        $user->phone = $request->input('phone', $user->phone);
        $user->bio = $request->input('bio', $user->bio);
        $user->company = $request->input('company', $user->company);

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^[0-9]{10}$/', $value)) {
                        $fail('The email or phone must be a valid email address or a 10-digit phone number.');
                    }
                }
            ],
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email_or_phone)
            ->orWhere('phone', $request->email_or_phone)
            ->first();

        // Prevents revealing whether the email/phone exists
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }


    public function getAllUsers()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        }

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ]);
    }

    // Get user by Userid
    public function getUserById($Userid)
    {
        $user = User::where('Userid', $Userid)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => $user
        ]);
    }
}
