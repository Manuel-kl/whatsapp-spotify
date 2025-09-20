<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function user(): JsonResponse
    {
        return response()->json([
            'message' => 'User retrieved successfully',
            'success' => true,
            'data' => Auth::user(),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users|min:7|max:15|regex:/^[0-9]+$/',
            'password' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'success' => true,
            'data' => $user,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:7|max:15|regex:/^[0-9]+$/',
            'password' => 'required|string|max:255',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'success' => false,
            ], 401);
        }

        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'success' => true,
            'data' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful',
            'success' => true,
        ]);
    }
}
