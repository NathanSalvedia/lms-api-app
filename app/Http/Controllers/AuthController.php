<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate the incoming request data
            $data = $request->validate([
                'unique_id' => 'required|integer',
                'fullname' => 'required|string',
                'email' => 'required|string|unique:users,email',
                'level' => 'required|string',
                'password' => 'required|string|confirmed',
                'gender' => 'required|string',
            ]);

            // Ensure a unique_id is always provided
            if (empty($data['unique_id'])) {
                // Custom method to generate unique ID if not provided
                $data['unique_id'] = $this->generateUniqueId();
            }

            // Check if the data is valid and not empty
            if (empty($data['fullname']) || empty($data['email'])) {
                return response()->json(['error' => 'Fullname and email are required fields.'], 400);
            }

            // Create the user
            $user = User::create([
                'unique_id' => $data['unique_id'],
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'level' => $data['level'],
                'password' => bcrypt($data['password']),
                'gender' => $data['gender']
            ]);

            // Create the token
            $token = $user->createToken('sanctum-token')->plainTextToken;

            // Prepare response data
            $response = [
                'user' => $user,
                'token' => $token
            ];

            // Return success response
            return response($response, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle general errors (e.g., database issues)
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
{
    try {
        // Validate the incoming request data
        $data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Check if the user exists
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Return an error if the credentials are invalid
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        // Create the token
        $token = $user->createToken('sanctum-token')->plainTextToken;

        // Prepare response data
        $response = [
            'user' => $user,
            'token' => $token
        ];

        // Return success response
        return response($response, 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'error' => 'Validation failed',
            'messages' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        // Handle general errors (e.g., database issues)
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage()
        ], 500);
    }
}




}
