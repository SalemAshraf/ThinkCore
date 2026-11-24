<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\PasswordUpdateRequest;
use App\Http\Requests\Frontend\ProfileUpdateRequest;
use App\Http\Requests\Frontend\SocialUpdateRequest;
use App\Models\User;
use App\Traits\FileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use FileUpload;

    public function index()
    {
        return redirect()->route('profile.edit');
    }

    public function instructorIndex()
    {
        return redirect()->route('profile.edit');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $newImage = null;
        $oldImage = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, &$newImage, &$oldImage) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                if ($request->hasFile('avatar')) {
                    $newImage = $this->uploadFile($request->file('avatar'), 'avatars');
                    $oldImage = $user->image;
                    $user->image = $newImage;
                }
                $user->name = $request->name;
                if ($user->email !== $request->email) $user->email_verified_at = null;
                $user->email = $request->email;
                $user->headline = $request->headline;
                $user->gender = $request->gender;
                $user->bio = $request->bio;
                $user->save();
                \Illuminate\Support\Facades\DB::afterCommit(function () use ($oldImage) {
                    try { $this->deleteFile($oldImage); } catch (\Throwable $cleanup) { report($cleanup); }
                });
            });
        } catch (\Throwable $exception) {
            try { $this->deleteFile($newImage); } catch (\Throwable $cleanup) { report($cleanup); }
            throw $exception;
        }

        noty()->layout('bottomCenter')->success('Profile updated successfully.');

        return redirect()->back()->with('status', 'تم حفظ التغييرات.');
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::find(Auth::id());

        if (! $user) {
            return back()->withErrors(['user' => 'User not found.']);
        }

        if (! Hash::check($request->current_password, (string) $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = $request->new_password;
        $user->save();
        noty()->layout('bottomCenter')->success('Password updated successfully.');

        return redirect()->back()->with('status', 'تم حفظ التغييرات.');
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

        return redirect()->back()->with('status', 'تم حفظ التغييرات.');
    }
}
