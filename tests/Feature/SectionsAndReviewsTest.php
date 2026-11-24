<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function sectionFor($course): int
{
    return DB::table('course_sections')->insertGetId(['course_id' => $course->id, 'title' => 'Section', 'position' => 1, 'created_at' => now(), 'updated_at' => now()]);
}

function enrollLearner($course): User
{
    $user = User::factory()->create();
    DB::table('course_enrollments')->insert(['course_id' => $course->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);

    return $user;
}

test('only owning instructor can create rename delete sections', function () {
    $course = publishedCourse();
    $section = sectionFor($course);
    $this->actingAs(instructor())->post('/instructor/courses/'.$course->id.'/sections', ['title' => 'Bad', 'position' => 1])->assertNotFound();
    $this->patch('/instructor/courses/'.$course->id.'/sections/'.$section, ['title' => 'Bad', 'position' => 1])->assertNotFound();
    $this->delete('/instructor/courses/'.$course->id.'/sections/'.$section)->assertNotFound();
    $this->actingAs(User::find($course->instructor_id))->post('/instructor/courses/'.$course->id.'/sections', ['title' => 'Valid section', 'position' => 2])->assertRedirect();
    expect($course->fresh()->status)->toBe('draft');
});

test('lesson section membership is limited to same course for create and update', function () {
    $course = publishedCourse();
    $foreignCourse = publishedCourse();
    $foreign = sectionFor($foreignCourse);
    $own = sectionFor($course);
    $lesson = courseLesson($course);
    $this->actingAs(User::find($course->instructor_id));
    $data = ['title' => 'Title', 'content' => 'Content', 'position' => 1, 'section_id' => $foreign];
    $this->postJson('/instructor/courses/'.$course->id.'/lessons', $data)->assertUnprocessable()->assertJsonValidationErrors('section_id');
    $this->patchJson('/instructor/courses/'.$course->id.'/lessons/'.$lesson, $data)->assertUnprocessable()->assertJsonValidationErrors('section_id');
    $data['section_id'] = $own;
    $this->patch('/instructor/courses/'.$course->id.'/lessons/'.$lesson, $data)->assertRedirect();
    expect(DB::table('course_lessons')->where('id', $lesson)->value('section_id'))->toBe($own);
    $this->delete('/instructor/courses/'.$course->id.'/sections/'.$own)->assertRedirect();
    expect(DB::table('course_lessons')->where('id', $lesson)->value('section_id'))->toBeNull();
    expect(DB::table('course_lessons')->where('id', $lesson)->exists())->toBeTrue();
});

test('sections are ordered publicly without disclosing lesson bodies', function () {
    $course = publishedCourse();
    $section = sectionFor($course);
    $lesson = courseLesson($course);
    DB::table('course_lessons')->where('id', $lesson)->update(['section_id' => $section]);
    $this->get('/courses/'.$course->slug)->assertOk()->assertSee('Section')->assertSee('First lesson')->assertDontSee('PRIVATE-LESSON-CONTENT');
});

test('guest unverified outsider and course owner cannot submit learner review', function () {
    $course = publishedCourse();
    $path = '/courses/'.$course->slug.'/reviews';
    $data = ['rating' => 5, 'body' => 'A detailed learner review'];
    $this->post($path, $data)->assertRedirect();
    $this->actingAs(User::factory()->unverified()->create())->post($path, $data)->assertRedirect(route('verification.notice'));
    $this->actingAs(User::factory()->create())->post($path, $data)->assertForbidden();
    $owner = User::find($course->instructor_id);
    DB::table('course_enrollments')->insert(['course_id' => $course->id, 'user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now()]);
    $this->actingAs($owner)->post($path, $data)->assertForbidden();
    expect(DB::table('course_reviews')->count())->toBe(0);
});

test('review ignores forged author and status validates values and keeps one row', function () {
    $course = publishedCourse();
    $student = enrollLearner($course);
    $path = '/courses/'.$course->slug.'/reviews';
    $this->actingAs($student)->postJson($path, ['rating' => 9, 'body' => 'x'])->assertUnprocessable()->assertJsonValidationErrors(['rating', 'body']);
    $this->post($path, ['rating' => 5, 'body' => 'A detailed learner review', 'user_id' => 999, 'status' => 'approved'])->assertRedirect();
    $row = DB::table('course_reviews')->first();
    expect($row->user_id)->toBe($student->id);
    expect($row->status)->toBe('pending');
    DB::table('course_reviews')->where('id', $row->id)->update(['status' => 'approved']);
    $this->post($path, ['rating' => 3, 'body' => 'An updated learner review'])->assertRedirect();
    expect(DB::table('course_reviews')->count())->toBe(1);
    expect(DB::table('course_reviews')->value('status'))->toBe('pending');
});

test('public reviews and average exclude pending and rejected content and escape markup', function () {
    $course = publishedCourse();
    foreach ([['pending', 5, 'PENDING-SECRET'], ['rejected', 1, 'REJECTED-SECRET'], ['approved', 4, '<script>approved-review()</script>']] as [$status,$rating,$body]) {
        $student = enrollLearner($course);
        DB::table('course_reviews')->insert(['course_id' => $course->id, 'user_id' => $student->id, 'status' => $status, 'rating' => $rating, 'body' => $body, 'created_at' => now(), 'updated_at' => now()]);
    }
    $this->get('/courses/'.$course->slug)->assertOk()->assertSee('4.0 / 5')->assertDontSee('PENDING-SECRET')->assertDontSee('REJECTED-SECRET')->assertSee('&lt;script&gt;approved-review()', false)->assertDontSee('<script>approved-review()', false);
});

test('only administrator may moderate pending learner reviews', function () {
    $course = publishedCourse();
    $student = enrollLearner($course);
    $this->actingAs($student)->post('/courses/'.$course->slug.'/reviews', ['rating' => 4, 'body' => 'A thoughtful course review']);
    $review = DB::table('course_reviews')->value('id');
    $this->get('/admin/learner-reviews')->assertRedirect();
    $this->post('/admin/learner-reviews/'.$review, ['decision' => 'approved', 'revision' => 1])->assertRedirect();
    expect(DB::table('course_reviews')->value('status'))->toBe('pending');
    $admin = Admin::create(['name' => 'Review admin', 'email' => 'review-admin@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->get('/admin/learner-reviews')->assertOk()->assertSee('A thoughtful course review');
    $this->post('/admin/learner-reviews/'.$review, ['decision' => 'approved', 'revision' => 1])->assertRedirect();
    expect(DB::table('course_reviews')->value('status'))->toBe('approved');
    $this->post('/admin/learner-reviews/'.$review, ['decision' => 'rejected', 'revision' => 1])->assertStatus(409);
});

test('stale moderator form cannot approve a learner edit made after review opened', function () {
    $course = publishedCourse();
    $student = enrollLearner($course);
    $path = '/courses/'.$course->slug.'/reviews';
    $this->actingAs($student)->post($path, ['rating' => 5, 'body' => 'Original review text']);
    $id = DB::table('course_reviews')->value('id');
    $this->post($path, ['rating' => 2, 'body' => 'Changed review text']);
    $admin = Admin::create(['name' => 'Moderator', 'email' => 'stale-admin@example.test', 'password' => 'test-password']);
    $this->actingAs($admin, 'admin')->post('/admin/learner-reviews/'.$id, ['decision' => 'approved', 'revision' => 1])->assertStatus(409);
    expect(DB::table('course_reviews')->value('status'))->toBe('pending');
    $this->post('/admin/learner-reviews/'.$id, ['decision' => 'approved', 'revision' => 2])->assertRedirect();
    expect(DB::table('course_reviews')->value('status'))->toBe('approved');
});
