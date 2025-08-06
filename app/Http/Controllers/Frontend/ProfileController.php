<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\PasswordUpdateRequest;
use App\Http\Requests\Frontend\ProfileUpdateRequest;
use App\Http\Requests\Frontend\SocialUpdateRequest;
use App\Models\User;
use App\Traits\FileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Passwords\Password;


class ProfileController extends Controller
{
    use FileUpload;
    public function index()
    {
        // Logic to display the user's profile
        return view('frontend.student-Dashboard.profile.index');
    }
    public function instructorIndex()
    {
        // Logic to display the user's profile
        return view('frontend.instructor-dashboard.profile.index');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = User::find(Auth::id());
        if($request->hasFile('avatar')) {
            $avatarPath = $this->uploadFile($request->file('avatar'), 'avatars');
            $this->deleteFile($user->image);
            $user->image = $avatarPath;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->headline = $request->headline;
        $user->gender = $request->gender;
        $user->bio = $request->bio;
        $user->save();

        noty()->layout('bottomCenter')->success('Profile updated successfully.');

        return redirect()->back();
    }
    public function updatePassword(PasswordUpdateRequest $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::find(Auth::id());

        if (!$user) {
            return back()->withErrors(['user' => 'User not found.']);
        }

        if (!Hash::check($request->current_password, (string) $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = $request->new_password;
        $user->save();
        noty()->layout('bottomCenter')->success('Password updated successfully.');

        return redirect()->back();
    }

    public function updateSocial(SocialUpdateRequest $request)
    {
        $user = User::find(Auth::id());
        $user->facebook = $request->facebook;
        $user->twitter = $request->twitter;
        $user->linkedin = $request->linkedin;
        $user->github = $request->github;
        $user->website = $request->website;
        $user->save();
        noty()->layout('bottomCenter')->success('Social links updated successfully.');

        return redirect()->back();
    }
}
