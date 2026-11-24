<?php

use Illuminate\Support\Facades\DB;

function orderedLesson($course, string $title, ?int $section, int $position, bool $preview = false): int
{
    return DB::table('course_lessons')->insertGetId(['course_id' => $course->id, 'title' => $title, 'content' => 'PRIVATE-'.$title, 'section_id' => $section, 'position' => $position, 'is_preview' => $preview, 'created_at' => now(), 'updated_at' => now()]);
}

test('lesson navigation follows section and lesson positions within same course', function () {
    $course = publishedCourse();
    $section = sectionFor($course);
    $second = orderedLesson($course, 'Second', $section, 2);
    $first = orderedLesson($course, 'First', $section, 1);
    $last = orderedLesson($course, 'Unsectioned', null, 1);
    $foreign = orderedLesson(publishedCourse(), 'Foreign', null, 1);
    $this->actingAs(enrollLearner($course))->get('/courses/'.$course->slug.'/lessons/'.$second)->assertOk()->assertSee(route('marketplace.lesson', [$course->slug, $first]), false)->assertSee(route('marketplace.lesson', [$course->slug, $last]), false)->assertDontSee('Foreign')->assertDontSee('PRIVATE-First')->assertDontSee('PRIVATE-Unsectioned');
});

test('guest preview navigation does not link protected neighboring lessons', function () {
    $course = publishedCourse();
    $private = orderedLesson($course, 'Protected title', null, 1);
    $preview = orderedLesson($course, 'Preview title', null, 2, true);
    $this->get('/courses/'.$course->slug.'/lessons/'.$preview)->assertOk()->assertDontSee(route('marketplace.lesson', [$course->slug, $private]), false);
});

test('learner library resumes first incomplete ordered lesson and shows completed state', function () {
    $course = publishedCourse();
    $first = orderedLesson($course, 'First', null, 1);
    $second = orderedLesson($course, 'Second', null, 2);
    $student = enrollLearner($course);
    $this->actingAs($student);
    $this->get('/my-learning')->assertOk()->assertSee(route('marketplace.lesson', [$course->slug, $first]), false);
    $this->post('/courses/'.$course->slug.'/lessons/'.$first.'/complete');
    $this->get('/courses/'.$course->slug.'/lessons/'.$first)->assertOk()->assertSee('أكملت هذا الدرس.')->assertDontSee('أنهيت هذا الدرس');
    $this->get('/my-learning')->assertOk()->assertSee(route('marketplace.lesson', [$course->slug, $second]), false);
    $this->post('/courses/'.$course->slug.'/lessons/'.$second.'/complete');
    $this->get('/my-learning')->assertOk()->assertSee('مراجعة الكورس');
});

test('catalog filters compose without exposing inactive paid or mismatched courses', function () {
    $category = DB::table('course_categories')->insertGetId(['name' => 'Programming', 'slug' => 'programming', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
    $level = DB::table('course_levels')->insertGetId(['name' => 'Beginner', 'slug' => 'beginner', 'created_at' => now(), 'updated_at' => now()]);
    $language = DB::table('course_languages')->insertGetId(['name' => 'Arabic', 'slug' => 'ar', 'created_at' => now(), 'updated_at' => now()]);
    $good = publishedCourse();
    $good->title = 'Matching free course';
    $good->category_id = $category;
    $good->course_level_id = $level;
    $good->course_language_id = $language;
    $good->save();
    $paid = publishedCourse(100);
    $paid->title = 'Excluded paid';
    $paid->save();
    $other = publishedCourse();
    $other->title = 'Excluded category';
    $other->save();
    $this->get('/courses?free=1&category='.$category.'&level='.$level.'&language='.$language)->assertOk()->assertSee('Matching free course')->assertDontSee('Excluded paid')->assertDontSee('Excluded category');
    $this->getJson('/courses?category[]=1')->assertUnprocessable();
    $this->getJson('/courses?q[]=x')->assertUnprocessable();
});
