<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        return app(LearningController::class)->library($request);
    }

    public function becomeInstructor()
    {
        return view('marketplace.instructor-application');
    }
    public function applyInstructor(Request $request)
    {
        $request->validate(['document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10000']]);
        $path = $request->file('document')->store('instructor-documents', 'local');
        $old = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $path, &$old) {
                $user = \App\Models\User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                abort_unless($user->role === 'student', 403);
                abort_if($user->approved_status === 'pending' && $user->document, 409, 'A request is already under review.');
                $old = $user->document;
                $user->document = $path;
                $user->approved_status = 'pending';
                $user->save();
            });
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
            throw $exception;
        }
        if ($old && str_starts_with($old, 'instructor-documents/') && !str_contains($old, '..')) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($old);
        }
        return back()->with('status', 'تم إرسال طلب التدريس للمراجعة. دور حسابك لا يتغير إلا بعد الموافقة.');
    }
}
