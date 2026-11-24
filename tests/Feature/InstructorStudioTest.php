<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('native instructor forms create update metadata and reload without JavaScript or duplicate courses', function () {
    $owner = instructor();
    $this->actingAs($owner);
    $data = ['title'=>'Native course','description'=>'A useful course description','price'=>0];
    $this->post('/instructor/courses/store', $data)->assertRedirect();
    $course = Course::sole();
    $slug = $course->slug;
    $this->get('/instructor/courses/'.$course->id.'/edit')->assertOk()->assertSee('action="'.route('instructor.courses.moreinfo').'"', false);
    $this->post('/instructor/courses/moreinfo', ['id'=>$course->id,'current_step'=>1]+array_replace($data,['title'=>'Updated native course']))->assertRedirect();
    expect(Course::count())->toBe(1);
    expect($course->fresh()->title)->toBe('Updated native course');
    expect($course->fresh()->slug)->toBe($slug);
    $category = DB::table('course_categories')->insertGetId(['name'=>'Root category','slug'=>'root-category','status'=>1]);
    $this->get('/instructor/courses/'.$course->id.'/edit?step=2')->assertOk()->assertSee('Root category');
    $this->post('/instructor/courses/moreinfo',['id'=>$course->id,'current_step'=>2,'category'=>$category,'duration'=>75])->assertRedirect(route('instructor.courses'));
    expect($course->fresh()->category_id)->toBe($category);
    expect($course->fresh()->duration)->toBe('75');
    $this->get('/instructor/courses/'.$course->id.'/edit?step=2')->assertOk()->assertSee('value="75"',false);
});

test('student instructor application remains private pending and cannot self-promote or duplicate', function () {
    Storage::fake('local');
    Storage::fake('public');
    $user = User::factory()->create(['role'=>'student','approved_status'=>'approved']);
    $this->actingAs($user)->get('/student/become-instructor')->assertOk();
    $this->post('/student/become-instructor',['document'=>UploadedFile::fake()->create('certificate.pdf',20,'application/pdf'),'role'=>'instructor','approved_status'=>'approved'])->assertRedirect();
    $user->refresh();
    expect($user->role)->toBe('student');
    expect($user->approved_status)->toBe('pending');
    Storage::disk('local')->assertExists($user->document);
    Storage::disk('public')->assertMissing($user->document);
    $path = $user->document;
    $this->post('/student/become-instructor',['document'=>UploadedFile::fake()->create('duplicate.pdf',20,'application/pdf')])->assertConflict();
    expect($user->fresh()->document)->toBe($path);
    expect(Storage::disk('local')->allFiles('instructor-documents'))->toHaveCount(1);
    $this->get('/instructor/courses')->assertRedirect();
});

test('instructor application rejects executable uploads and allows rejected user retry only', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create(['role'=>'student','approved_status'=>'rejected']));
    $this->post('/student/become-instructor',['document'=>UploadedFile::fake()->create('bad.php',20,'text/plain')])->assertSessionHasErrors('document');
    expect(Storage::disk('local')->allFiles('instructor-documents'))->toBeEmpty();
    $this->post('/student/become-instructor',['document'=>UploadedFile::fake()->create('good.pdf',20,'application/pdf')])->assertRedirect();
    $this->actingAs(instructor())->post('/student/become-instructor',['document'=>UploadedFile::fake()->create('good.pdf',20,'application/pdf')])->assertRedirect();
});

test('coherent profile and authentication pages retain functional routes and named validation errors', function () {
    $this->get('/forgot-password')->assertOk()->assertSee('marketplace/thinkcore.css')->assertSee(route('password.email'));
    $this->get('/reset-password/token?email=learner@example.test')->assertOk()->assertSee('name="token" value="token"',false);
    $this->actingAs(User::factory()->unverified()->create())->get('/verify-email')->assertOk()->assertSee(route('verification.send'));
    $this->get('/profile')->assertOk()->assertSee(route('password.update'))->assertSee(route('profile.destroy'));
    $this->put('/password',['current_password'=>'wrong','password'=>'new-password','password_confirmation'=>'new-password'])->assertSessionHasErrorsIn('updatePassword','current_password');
    $this->get('/profile')->assertOk()->assertSee('error');
});

test('instructor cannot delete a course-owning account and break learner content', function () {
    $owner = instructor();
    draftFor($owner);
    $this->actingAs($owner)->delete('/profile',['password'=>'password'])->assertRedirect()->assertSessionHasErrorsIn('userDeletion','password');
    $this->assertAuthenticatedAs($owner);
    expect($owner->fresh())->not->toBeNull();
});

test('profile detail and social forms persist fields without role changes and old profile URLs lead to settings', function () {
    $user = instructor();
    $this->actingAs($user)->get('/instructor/profile')->assertRedirect(route('profile.edit'));
    $this->post('/instructor/profile/update',['name'=>$user->name,'email'=>$user->email,'headline'=>'Web instructor','gender'=>'male','bio'=>'About the instructor','role'=>'admin'])->assertRedirect();
    $this->post('/instructor/profile/social',['website'=>'https://example.test','github'=>'https://github.com/example'])->assertRedirect();
    $user->refresh();
    expect($user->headline)->toBe('Web instructor');
    expect($user->bio)->toBe('About the instructor');
    expect($user->website)->toBe('https://example.test');
    expect($user->role)->toBe('instructor');
    $this->get('/profile')->assertOk()->assertSee('Web instructor')->assertSee('https://example.test');
});
