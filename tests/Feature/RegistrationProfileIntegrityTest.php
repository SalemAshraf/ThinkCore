<?php

use App\Models\User;
use App\Mail\InstructorRequestApprovalMail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

function applicationPayload(): array {
    return ['name'=>'Fictional applicant','email'=>'registration-integrity@example.test','password'=>'password123','password_confirmation'=>'password123','type'=>'instructor','document'=>UploadedFile::fake()->create('qualification.pdf',10,'application/pdf')];
}

test('failed instructor account persistence removes new private upload and does not authenticate or notify', function () {
    Storage::fake('local');Mail::fake();Notification::fake();
    User::creating(function(){throw new RuntimeException('Injected insert failure');});
    try {$this->post('/register',applicationPayload())->assertServerError();} finally {User::flushEventListeners();}
    expect(User::count())->toBe(0);expect(Storage::disk('local')->allFiles('instructor-documents'))->toBe([]);
    $this->assertGuest();Mail::assertNothingQueued();Notification::assertNothingSent();
});

test('queue failure after registration preserves account login and private document with truthful warning', function () {
    Storage::fake('local');Notification::fake();Mail::shouldReceive('to')->andThrow(new RuntimeException('Injected transport failure'));
    $this->post('/register',applicationPayload())->assertRedirect('/')->assertSessionHas('status');
    $user=User::sole();$this->assertAuthenticatedAs($user);
    expect($user->role)->toBe('student');expect($user->approved_status)->toBe('pending');Storage::disk('local')->assertExists($user->document);
    Notification::assertSentTo($user,Illuminate\Auth\Notifications\VerifyEmail::class);
});

test('registration queued pending notification snapshots name and status instead of mutable user', function () {
    Storage::fake('local');Mail::fake();Notification::fake();
    $this->post('/register',applicationPayload())->assertRedirect('/');$user=User::sole();
    $user->name='Later name';$user->approved_status='approved';$user->save();
    Mail::assertQueued(InstructorRequestApprovalMail::class,function($mail) {
        $restored=unserialize(serialize($mail));
        return $restored->instructor->name==='Fictional applicant' && $restored->instructor->approved_status==='pending';
    });
});

test('verification transport failure does not make committed student registration fail', function () {
    Mail::fake();Notification::shouldReceive('send')->andThrow(new RuntimeException('Injected verification failure'));
    $this->post('/register',['name'=>'Student','email'=>'student-integrity@example.test','password'=>'password123','password_confirmation'=>'password123','type'=>'student'])->assertRedirect('/')->assertSessionHas('status');
    $this->assertAuthenticatedAs(User::sole());expect(User::sole()->email_verified_at)->toBeNull();
});

test('failed profile save keeps old avatar bytes and removes new avatar', function () {
    Storage::fake('public');Storage::disk('public')->put('avatars/original.png','original bytes');
    $user=instructor();$user->image='/storage/avatars/original.png';$user->save();
    User::updating(function(){throw new RuntimeException('Injected update failure');});
    try {$this->actingAs($user)->post('/instructor/profile/update',['name'=>'Changed','email'=>$user->email,'headline'=>'Teacher','gender'=>'male','avatar'=>UploadedFile::fake()->image('new.png')])->assertServerError();} finally {User::flushEventListeners();}
    expect($user->fresh()->image)->toBe('/storage/avatars/original.png');expect($user->fresh()->name)->toBe($user->name);
    expect(Storage::disk('public')->allFiles('avatars'))->toBe(['avatars/original.png']);expect(Storage::disk('public')->get('avatars/original.png'))->toBe('original bytes');
});

test('successful profile save persists new avatar before retiring old and clears changed email verification', function () {
    Storage::fake('public');Storage::disk('public')->put('avatars/previous.png','old');
    $user=instructor();$user->image='/storage/avatars/previous.png';$user->save();
    $this->actingAs($user)->post('/instructor/profile/update',['name'=>'Updated','email'=>'new-integrity@example.test','headline'=>'Teacher','gender'=>'male','avatar'=>UploadedFile::fake()->image('new.png')])->assertRedirect();
    $fresh=$user->fresh();expect($fresh->email_verified_at)->toBeNull();expect($fresh->name)->toBe('Updated');
    Storage::disk('public')->assertExists(substr($fresh->image,9));Storage::disk('public')->assertMissing('avatars/previous.png');
});
