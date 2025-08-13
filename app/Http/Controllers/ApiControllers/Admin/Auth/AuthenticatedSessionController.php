<?php

namespace App\Http\Controllers\ApiControllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;


class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming API authentication request.
     */



    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

        // Check password
        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Create Sanctum token
        $token = $admin->createToken('admin_api_token')->plainTextToken;

        // Return success response
        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ]
        ]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user('admin-api');

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
