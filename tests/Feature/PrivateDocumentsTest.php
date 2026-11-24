<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('instructor application stores accepted document privately and rejects executable upload', function () {
    Storage::fake('local');
    Storage::fake('public');
    Mail::fake();
    $this->post('/register', ['name' => 'Applicant', 'email' => 'applicant@example.test', 'password' => 'test-password', 'password_confirmation' => 'test-password', 'type' => 'instructor', 'document' => UploadedFile::fake()->create('identity.pdf', 20, 'application/pdf')])->assertRedirect();
    $user = User::where('email', 'applicant@example.test')->firstOrFail();
    expect($user->document)->toStartWith('instructor-documents/');
    Storage::disk('local')->assertExists($user->document);
    Storage::disk('public')->assertMissing($user->document);
    $this->post('/logout');
    $this->post('/register', ['name' => 'Bad applicant', 'email' => 'bad-applicant@example.test', 'password' => 'test-password', 'password_confirmation' => 'test-password', 'type' => 'instructor', 'document' => UploadedFile::fake()->create('shell.php', 20, 'text/plain')])->assertSessionHasErrors('document');
    expect(User::where('email', 'bad-applicant@example.test')->exists())->toBeFalse();
});

test('document route requires administrator and refuses legacy public arbitrary paths', function () {
    Storage::fake('local');
    Storage::disk('local')->put('instructor-documents/sample.pdf', 'synthetic-document');
    $user = User::factory()->create(['document' => 'instructor-documents/sample.pdf']);
    $this->get('/admin/document-download/'.$user->id)->assertRedirect();
    $this->actingAs(User::factory()->create())->get('/admin/document-download/'.$user->id)->assertRedirect();
    $admin = Admin::create(['name' => 'Doc reviewer', 'email' => 'doc-admin@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->get('/admin/document-download/'.$user->id)->assertDownload('sample.pdf');
    $user->document = '/uploads/legacy.pdf';
    $user->save();
    $this->get('/admin/document-download/'.$user->id)->assertNotFound();
    $user->document = 'instructor-documents/../secrets';
    $user->save();
    $this->get('/admin/document-download/'.$user->id)->assertNotFound();
});

test('disabled public filemanager is not reachable by instructor', function () {
    $this->actingAs(instructor())->get('/instructor/laravel-filemanager')->assertNotFound();
});
