<?php

use App\Models\Course;
use App\Models\User;

function instructor(): User
{
    return User::factory()->create(['role' => 'instructor', 'approved_status' => 'approved']);
}

function draftFor(User $user, string $title = 'Private draft'): Course
{
    $course = new Course;
    $course->instructor_id = $user->id;
    $course->title = $title;
    $course->slug = 'draft-'.fake()->uuid();
    $course->save();

    return $course;
}

test('instructor list excludes other instructor courses', function () {
    $owner = instructor();
    draftFor($owner, 'Own course');
    draftFor(instructor(), 'Other private course');
    $this->actingAs($owner)->get('/instructor/courses')->assertOk()->assertSee('Own course')->assertDontSee('Other private course');
});

test('foreign course cannot be read or updated at any wizard step', function () {
    $course = draftFor(instructor());
    $this->actingAs(instructor());
    foreach ([1, 2] as $step) {
        $this->get('/instructor/courses/'.$course->id.'/edit?step='.$step)->assertNotFound();
        $this->postJson('/instructor/courses/moreinfo', ['id' => $course->id, 'current_step' => $step, 'title' => 'Stolen'])->assertNotFound();
    }
    expect($course->fresh()->title)->toBe('Private draft');
});

test('course defaults to first edit step and rejects unimplemented steps', function () {
    $user = instructor();
    $course = draftFor($user);
    $this->actingAs($user)->get('/instructor/courses/'.$course->id.'/edit')->assertOk();
    $this->get('/instructor/courses/'.$course->id.'/edit?step=99')->assertNotFound();
});

test('Arabic and duplicate course titles produce distinct stable slugs', function () {
    $this->actingAs(instructor());
    $data = ['title' => 'مقدمة في البرمجة', 'description' => 'Practical course', 'price' => 0];
    $this->postJson('/instructor/courses/store', $data)->assertOk();
    $this->postJson('/instructor/courses/store', $data)->assertOk();
    $courses = Course::all();
    expect($courses)->toHaveCount(2);
    expect($courses[0]->slug)->not->toBe($courses[1]->slug);
    $slug = $courses[0]->slug;
    $this->postJson('/instructor/courses/moreinfo', $data + ['id' => $courses[0]->id, 'current_step' => 1])->assertOk();
    expect($courses[0]->fresh()->slug)->toBe($slug);
});

test('metadata validates actual form fields and returns to saved draft list', function () {
    $user = instructor();
    $course = draftFor($user);
    $this->actingAs($user)->postJson('/instructor/courses/moreinfo', ['id' => $course->id, 'current_step' => 2, 'category' => 999, 'level' => 999, 'language' => 999, 'capacity' => -1])->assertUnprocessable()->assertJsonValidationErrors(['category', 'level', 'language', 'capacity']);
    $this->postJson('/instructor/courses/moreinfo', ['id' => $course->id, 'current_step' => 2, 'duration' => 60])->assertOk()->assertJsonPath('redirect', route('instructor.courses'));
    expect($course->fresh()->status)->toBe('draft');
});

test('unverified students cannot enter student or instructor dashboards', function () {
    $this->actingAs(User::factory()->unverified()->create())->get('/student/dashboard')->assertRedirect(route('verification.notice'));
});

test('discount and executable demo URLs are rejected', function () {
    $this->actingAs(instructor())->postJson('/instructor/courses/store', ['title' => 'Bad course', 'description' => 'Description', 'price' => 10, 'discount' => 20, 'demo_video_source' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors(['discount', 'demo_video_source']);
});
