<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InstructorRequestApprovalMail;
use App\Models\User;
use App\Traits\FileUpload;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use FileUpload;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->validate(['type' => ['required', 'in:student,instructor']]);
        $path = null;
        if ($request->type === 'instructor') {
            $request->validate(['document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10000']]);
            $path = $request->file('document')->store('instructor-documents', 'local');
            abort_unless($path, 503, 'Document storage is unavailable.');
        }
        try {
            $user = \Illuminate\Support\Facades\DB::transaction(fn () => User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'approved_status' => $request->type === 'instructor' ? 'pending' : 'approved',
                'document' => $path,
            ]));
        } catch (\Throwable $exception) {
            if ($path) {
                try { \Illuminate\Support\Facades\Storage::disk('local')->delete($path); }
                catch (\Throwable $cleanup) { report($cleanup); }
            }
            throw $exception;
        }
        // Transport/queue failures after account commit must not make a user
        // retry registration for an account that already exists.
        $notificationFailed = false;
        if ($request->type === 'instructor') {
            try {
                Mail::to($user->email)->queue(new InstructorRequestApprovalMail((object)[
                    'name'=>$user->name, 'approved_status'=>'pending',
                ]));
            } catch (\Throwable $error) {
                $notificationFailed = true;
                \Illuminate\Support\Facades\Log::warning('Registration pending notification failed.', ['user_id'=>$user->id,'error_class'=>get_class($error)]);
            }
        }
        try { event(new Registered($user)); }
        catch (\Throwable $error) {
            $notificationFailed = true;
            \Illuminate\Support\Facades\Log::warning('Registration verification notification failed.', ['user_id'=>$user->id,'error_class'=>get_class($error)]);
        }

        Auth::login($user);

        if (! $notificationFailed) noty()->layout('bottomCenter')->success('Registration successful. Please check your email for further instructions.');

        $response = redirect(route('home', absolute: false));
        if ($notificationFailed) $response->with('status', 'تم إنشاء حسابك، لكن تعذّر إرسال إحدى رسائل البريد. لا تعِد التسجيل. يمكنك طلب رسالة التحقق من صفحة تأكيد البريد.');
        return $response;
    }
}
