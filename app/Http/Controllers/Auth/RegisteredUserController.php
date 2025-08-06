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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if($request->type === 'student') {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'approved_status' => 'approved',
            ]);
        }else if($request->type === 'instructor') {
            $request->validate(['document' => ['required', 'file', 'max:10000']]);
            $filepath = $this->uploadFile($request->file('document'));
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'approved_status' => 'pending',
                'document' => $filepath,
            ]);
            Mail::to($user->email)->queue(new InstructorRequestApprovalMail($user));
        }else{
            abort(403, 'Unauthorized action.');
        }


        event(new Registered($user));

        Auth::login($user);

        noty()->layout('bottomCenter')->success('Registration successful. Please check your email for further instructions.');

        return redirect(route('home', absolute: false));
    }
}
