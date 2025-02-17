<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // User Registration with Passport
    public function register(Request $request)
    {
        try {
            $fields = $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|confirmed'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        // Create the user
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'is_approved' => false,
        ]);
        // Assign default role (e.g., Student)
        $user->roles()->attach(Role::where('name', 'student')->first());
        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }
    // User Login with Passport
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required'
            ]);

            // Check credentials
            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }

            // Get authenticated user
            $user = Auth::user();

            // Check if user is approved
            if (!$user->is_approved) {
                Auth::logout();
                return response()->json(['error' => 'Your account is pending approval.'], 403);
            }

            // Generate Passport Token
            $token = $user->createToken('Quiz_App')->accessToken;

            return response()->json([
                'message' => 'Login successful!',
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // User Logout
    public function logout(Request $request)
    {
        try {
            // Check if user is authenticated via API guard
            if (Auth::guard('api')->check()) {
                $user = $request->user();

                // Revoke the current access token
                $token = $user->token();
                if ($token) {
                    $token->revoke(); // Revoke the token (Passport-specific)
                    return response()->json(['message' => 'Logged out successfully!']);
                }
            }
            return response()->json(['message' => 'User not logged in!'], 401);
        } catch (\Exception $e) {
            \Log::error('Logout Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error during logout'], 500);
        }
    }
}
