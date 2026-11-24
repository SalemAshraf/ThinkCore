<?php

use App\Models\Admin;
use App\Models\Course;
use App\Models\User;
use App\Support\CourseReviewSnapshot;
use Illuminate\Support\Facades\DB;

function publishedCourse(float $price = 0): Course
{
    $course = draftFor(instructor());
    $course->description = 'A real course description';
    $course->price = $price;
    $course->status = 'active';
    $course->is_approved = 'approved';
    $course->save();

    return $course;
}

function courseLesson($course, bool $preview = false): int
{
    return DB::table('course_lessons')->insertGetId(['course_id' => $course->id, 'title' => 'First lesson', 'content' => 'PRIVATE-LESSON-CONTENT', 'position' => 1, 'is_preview' => $preview, 'created_at' => now(), 'updated_at' => now()]);
}

test('catalog contains only approved active courses and no protected lesson content', function () {
    $course = publishedCourse();
    courseLesson($course);
    draftFor(instructor(), 'Hidden draft');
    $this->get('/courses')->assertOk()->assertSee($course->title)->assertDontSee('Hidden draft')->assertDontSee('PRIVATE-LESSON-CONTENT');
    $this->get('/courses/'.$course->slug)->assertOk()->assertDontSee('PRIVATE-LESSON-CONTENT');
});

test('guest cannot read protected lessons but may read previews', function () {
    $course = publishedCourse();
    $private = courseLesson($course);
    $preview = courseLesson($course, true);
    $this->get('/courses/'.$course->slug.'/lessons/'.$private)->assertForbidden()->assertDontSee('PRIVATE-LESSON-CONTENT');
    $this->get('/courses/'.$course->slug.'/lessons/'.$preview)->assertOk()->assertSee('PRIVATE-LESSON-CONTENT');
});

test('free enrollment is idempotent and enables lesson completion and own library', function () {
    $course = publishedCourse();
    $lesson = courseLesson($course);
    $student = User::factory()->create();
    $this->actingAs($student);
    for ($i = 0; $i < 2; $i++) {
        $this->post('/courses/'.$course->slug.'/enroll')->assertRedirect();
        $this->post('/courses/'.$course->slug.'/lessons/'.$lesson.'/complete')->assertRedirect();
    }
    expect(DB::table('course_enrollments')->count())->toBe(1);
    expect(DB::table('lesson_completions')->count())->toBe(1);
    $this->get('/courses/'.$course->slug.'/lessons/'.$lesson)->assertOk()->assertSee('PRIVATE-LESSON-CONTENT');
    $this->get('/my-learning')->assertOk()->assertSee($course->title);
    $this->actingAs(User::factory()->create())->get('/my-learning')->assertOk()->assertDontSee($course->title);
});

test('browser cannot make a paid course free or mark unowned lessons complete', function () {
    $course = publishedCourse(100);
    $lesson = courseLesson($course);
    $this->actingAs(User::factory()->create())->post('/courses/'.$course->slug.'/enroll', ['price' => 0, 'discount' => 100])->assertForbidden();
    $this->post('/courses/'.$course->slug.'/lessons/'.$lesson.'/complete')->assertForbidden();
    expect(DB::table('course_enrollments')->count())->toBe(0);
});

test('lesson lookup is bound to its course even when enrolled elsewhere', function () {
    $course = publishedCourse();
    $other = publishedCourse();
    $foreign = courseLesson($other);
    $this->actingAs(User::factory()->create())->post('/courses/'.$course->slug.'/enroll');
    $this->get('/courses/'.$course->slug.'/lessons/'.$foreign)->assertNotFound();
    $this->post('/courses/'.$course->slug.'/lessons/'.$foreign.'/complete')->assertNotFound();
});

test('only owning instructor may add lessons or submit for review', function () {
    $owner = instructor();
    $course = draftFor($owner);
    $this->actingAs(instructor())->post('/instructor/courses/'.$course->id.'/lessons', ['title' => 'Bad', 'content' => 'Injected', 'position' => 1])->assertNotFound();
    $this->post('/instructor/courses/'.$course->id.'/submit')->assertNotFound();
    $this->actingAs($owner)->post('/instructor/courses/'.$course->id.'/lessons', ['title' => 'Good lesson', 'content' => '<script>bad()</script>', 'position' => 1])->assertRedirect();
    expect(DB::table('course_lessons')->count())->toBe(1);
    $this->get('/instructor/courses/'.$course->id.'/curriculum')->assertOk();
});

test('admin review publishes complete submitted course and content edits remove publication', function () {
    $course = publishedCourse();
    $lesson = courseLesson($course);
    $course->status = 'inactive';
    $course->is_approved = 'pending';
    $course->save();
    $admin = Admin::create(['name' => 'Reviewer', 'email' => 'reviewer@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->get('/admin/course-reviews')->assertOk()->assertSee($course->title);
    $this->post('/admin/course-reviews/'.$course->id, ['decision' => 'approved', 'snapshot' => CourseReviewSnapshot::token($course->fresh())])->assertRedirect();
    expect($course->fresh()->status)->toBe('active');
    $this->actingAs(User::find($course->instructor_id), 'web')->post('/instructor/courses/'.$course->id.'/lessons', ['title' => 'New content', 'content' => 'Needs review', 'position' => 2])->assertRedirect();
    expect($course->fresh()->status)->toBe('draft');
    $this->get('/courses/'.$course->slug)->assertNotFound();
});

test('lesson mutations enforce ownership and escape submitted markup', function () {
    $course = publishedCourse();
    $lesson = courseLesson($course, true);
    $this->actingAs(instructor())->patch('/instructor/courses/'.$course->id.'/lessons/'.$lesson, ['title' => 'Hijacked', 'content' => 'Bad', 'position' => 1])->assertNotFound();
    $this->delete('/instructor/courses/'.$course->id.'/lessons/'.$lesson)->assertNotFound();
    $this->actingAs(User::find($course->instructor_id))->patch('/instructor/courses/'.$course->id.'/lessons/'.$lesson, ['title' => 'Safe', 'content' => '<script>alert(1)</script>', 'position' => 2, 'is_preview' => 1])->assertRedirect();
    expect($course->fresh()->status)->toBe('draft');
    $course->refresh();
    $course->status = 'active';
    $course->is_approved = 'approved';
    $course->save();
    $this->get('/courses/'.$course->slug.'/lessons/'.$lesson)->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->delete('/instructor/courses/'.$course->id.'/lessons/'.$lesson)->assertRedirect();
    expect(DB::table('course_lessons')->where('id', $lesson)->exists())->toBeFalse();
});

test('student cannot approve own course or bypass instructor routes', function () {
    $course = publishedCourse();
    $this->actingAs(User::factory()->create())->post('/admin/course-reviews/'.$course->id, ['decision' => 'approved', 'snapshot' => CourseReviewSnapshot::token($course->fresh())])->assertRedirect();
    $this->post('/instructor/courses/'.$course->id.'/lessons', ['title' => 'No', 'content' => 'No', 'position' => 1])->assertRedirect('/');
});

test('free course capacity is enforced without blocking an existing enrollment retry', function () {
    $course = publishedCourse();
    $course->capacity = 1;
    $course->save();
    $student = User::factory()->create();
    $this->actingAs($student)->post('/courses/'.$course->slug.'/enroll')->assertRedirect();
    $this->post('/courses/'.$course->slug.'/enroll')->assertRedirect();
    $this->actingAs(User::factory()->create())->post('/courses/'.$course->slug.'/enroll')->assertStatus(409);
    expect(DB::table('course_enrollments')->where('course_id', $course->id)->count())->toBe(1);
});

test('unverified enrolled user cannot retrieve protected lesson content', function () {
    $course = publishedCourse();
    $lesson = courseLesson($course);
    $student = User::factory()->unverified()->create();
    DB::table('course_enrollments')->insert(['course_id' => $course->id, 'user_id' => $student->id, 'created_at' => now(), 'updated_at' => now()]);
    $this->actingAs($student)->get('/courses/'.$course->slug.'/lessons/'.$lesson)->assertForbidden();
});

test('instructor cannot read another owners curriculum and draft is never public', function () {
    $course = draftFor(instructor());
    $lesson = courseLesson($course, true);
    $this->actingAs(instructor())->get('/instructor/courses/'.$course->id.'/curriculum')->assertNotFound();
    $this->get('/courses/'.$course->slug)->assertNotFound();
    $this->get('/courses/'.$course->slug.'/lessons/'.$lesson)->assertNotFound();
});

test('empty course cannot be submitted or approved', function () {
    $owner = instructor();
    $course = draftFor($owner);
    $this->actingAs($owner)->post('/instructor/courses/'.$course->id.'/submit')->assertStatus(422);
    $course->status = 'inactive';
    $course->save();
    $admin = Admin::create(['name' => 'Reviewer', 'email' => 'empty-reviewer@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->post('/admin/course-reviews/'.$course->id, ['decision' => 'approved', 'snapshot' => CourseReviewSnapshot::token($course->fresh())])->assertStatus(422);
    expect($course->fresh()->is_approved)->toBe('pending');
});

test('stale course approval cannot publish changed resubmitted lesson content', function () {
    $course = publishedCourse();
    $lesson = courseLesson($course);
    $course->status = 'inactive';
    $course->is_approved = 'pending';
    $course->save();
    $snapshot = CourseReviewSnapshot::token($course->fresh());
    $this->actingAs(User::find($course->instructor_id))->patch('/instructor/courses/'.$course->id.'/lessons/'.$lesson, ['title' => 'Updated title', 'content' => 'Changed material requiring new review', 'position' => 1])->assertRedirect();
    $this->post('/instructor/courses/'.$course->id.'/submit')->assertRedirect();
    $admin = Admin::create(['name' => 'Snapshot reviewer', 'email' => 'snapshot@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->post('/admin/course-reviews/'.$course->id, ['decision' => 'approved', 'snapshot' => $snapshot])->assertStatus(409);
    expect($course->fresh()->status)->toBe('inactive');
    $this->post('/admin/course-reviews/'.$course->id, ['decision' => 'approved', 'snapshot' => CourseReviewSnapshot::token($course->fresh())])->assertRedirect();
    expect($course->fresh()->status)->toBe('active');
});

test('course decision requires exact review snapshot and snapshot includes metadata and sections', function () {
    $course = publishedCourse();
    courseLesson($course);
    $course->status = 'inactive';
    $course->is_approved = 'pending';
    $course->save();
    $original = CourseReviewSnapshot::token($course->fresh());
    sectionFor($course);
    expect(CourseReviewSnapshot::token($course->fresh()))->not->toBe($original);
    $before = CourseReviewSnapshot::token($course->fresh());
    $course->price = 99;
    $course->save();
    expect(CourseReviewSnapshot::token($course->fresh()))->not->toBe($before);
    $admin = Admin::create(['name' => 'Token reviewer', 'email' => 'token@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->postJson('/admin/course-reviews/'.$course->id, ['decision' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors('snapshot');
    $this->get('/admin/course-reviews')->assertOk()->assertSee('name="snapshot"', false);
});
