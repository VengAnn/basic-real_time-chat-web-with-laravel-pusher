<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
            'confirmPassword' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password
        ]);

        Auth::login($user);

        return response()->json([
            'status' => 200,
            'message' => 'User registered successfully',
            'user' => $user,
        ], 200);
    }

    // Login an existing user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->errors(),
            ], 422);
        }

        // Attempt to log the user in
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Authentication passed
            $user = Auth::user();
            return response()->json([
                'status' => 200,
                'message' => 'Login successful',
                'user' => $user,
            ], 200);
        }

        // Authentication failed
        return response()->json([
            'status' => '401',
            'message' => 'Invalid email or password',
        ], 401);
    }

    // Logout the authenticated user
    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    // public function getAllUsers()
    // {
    //     // Retrieve all users from the 'users' table
    //     $users = User::all();

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'All users retrieved successfully',
    //         'users' => $users,
    //     ], 200);
    // }

    public function getAllUsers(Request $request)
    {
        $search = $request->input('search');

        // Query to get users based on the search term
        if ($search) {
            $users = User::where('name', 'like', "%$search%")->get();
        } else {
            $users = User::all();
        }

        return response()->json(['users' => $users]);
    }


}
