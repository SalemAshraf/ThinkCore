<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('home uses the real published catalog and never template social proof', function () {
    $published = publishedCourse();
    $hidden = draftFor(instructor(), 'DO-NOT-PUBLISH-DRAFT');
    $this->get('/')->assertOk()->assertSee($published->title)->assertDontSee($hidden->title)
        ->assertSee('marketplace/thinkcore.css')->assertDontSee('$2456.34');
});

test('student dashboard displays real lesson progress instead of template earnings', function () {
    $course = publishedCourse();
    $a = courseLesson($course);
    courseLesson($course);
    courseLesson($course);
    $user = User::factory()->create();
    DB::table('course_enrollments')->insert(['course_id'=>$course->id,'user_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('lesson_completions')->insert(['course_lesson_id'=>$a,'user_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
    $this->actingAs($user)->get('/student/dashboard')->assertOk()->assertSee($course->title)
        ->assertSee('aria-valuenow="33"', false)->assertSee('width:33%', false)
        ->assertDontSee('$2456.34')->assertDontSee('16,450');
});

test('instructor overview scopes course counts and content to owner', function () {
    $owner = instructor();
    $own = draftFor($owner, 'MY-COURSE');
    $other = draftFor(instructor(), 'OTHER-INSTRUCTOR-COURSE');
    $this->actingAs($owner)->get('/instructor/dashboard')->assertOk()->assertSee($own->title)
        ->assertDontSee($other->title)->assertDontSee('$2456.34')->assertDontSee('16,450');
});

test('enrolled course displays continuation and catalog escapes untrusted course metadata', function () {
    $course = publishedCourse();
    $course->title = '<script>alert(1)</script>';
    $course->thumbnail = 'https://untrusted.invalid/tracker.png';
    $course->save();
    $user = User::factory()->create();
    $this->actingAs($user)->post('/courses/'.$course->slug.'/enroll')->assertRedirect();
    $this->get('/courses/'.$course->slug)->assertOk()->assertSee('أنت مشترك في هذا الكورس')
        ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('https://untrusted.invalid/tracker.png', false);
});

test('register preserves selected instructor path and private document upload', function () {
    $this->get('/register?type=instructor')->assertOk()->assertSee('name="document"', false)
        ->assertSee('type=instructor')->assertDontSee('href="#"', false);
    $this->get('/login')->assertOk()->assertSee('autocomplete="current-password"', false)
        ->assertDontSee('href="#"', false);
});
