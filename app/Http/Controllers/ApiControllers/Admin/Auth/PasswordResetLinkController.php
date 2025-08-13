<?php

namespace App\Http\Controllers\ApiControllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request for Admin API.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                $notification = new ResetPassword($token);
                $notification->createUrlUsing(function () use ($token, $user) {
                    return config('app.frontend_url') . "/admin/reset-password?token={$token}&email={$user->email}";
                });
                $user->notify($notification);
            }
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => __($status)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __($status)
        ], 400);
    }
}
