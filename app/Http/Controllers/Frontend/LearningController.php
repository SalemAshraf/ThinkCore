<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningController extends Controller
{
    public function catalog(Request $request)
    {
        $query = Course::where('status', 'active')->where('is_approved', 'approved');
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 120);
        if ($term !== '') {
            $query->where('title', 'like', '%'.addcslashes($term, '%_\\').'%');
        }
        $courses = $query->latest()->paginate(12)->withQueryString();

        return view('marketplace.catalog', compact('courses', 'term'));
    }

    public function show(Request $request, string $slug)
    {
        $course = $this->published($slug);
        // Never hydrate lesson bodies into a public page.
        $lessons = DB::table('course_lessons')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get(['id', 'title', 'is_preview', 'section_id']);

        $sections = DB::table('course_sections')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get();
        $reviews = DB::table('course_reviews')->where('course_id', $course->id)->where('status', 'approved')->latest('id')->paginate(10);
        $rating = DB::table('course_reviews')->where('course_id', $course->id)->where('status', 'approved')->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
        $canReview = $this->enrolled($course, $request) && $request->user()->id !== $course->instructor_id;
        $ownReview = $request->user() ? DB::table('course_reviews')->where('course_id', $course->id)->where('user_id', $request->user()->id)->first() : null;

        return view('marketplace.course', compact('course', 'lessons', 'sections', 'reviews', 'rating', 'canReview', 'ownReview'));
    }

    public function enroll(Request $request, string $slug)
    {
        $course = $this->published($slug);
        // Paid orders are not implemented. Ignore all browser-supplied prices.
        abort_unless($course->price !== null && (float) $course->price === 0.0, 403, 'Paid enrollment is not available yet.');
        DB::transaction(function () use ($course, $request) {
            $locked = Course::whereKey($course->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'active' && $locked->is_approved === 'approved' && $locked->price !== null && (float) $locked->price === 0.0, 403);
            if (! $this->enrolled($locked, $request)) {
                abort_if($locked->capacity && DB::table('course_enrollments')->where('course_id', $locked->id)->count() >= $locked->capacity, 409, 'Course capacity reached.');
                DB::table('course_enrollments')->insertOrIgnore(['course_id' => $locked->id, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        return redirect()->route('marketplace.course', $course->slug)->with('status', 'You are enrolled.');
    }

    public function lesson(Request $request, string $slug, int $lesson)
    {
        $course = $this->published($slug);
        $lesson = DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->first();
        abort_unless($lesson, 404);
        $enrolled = $this->enrolled($course, $request);
        abort_unless($lesson->is_preview || $enrolled || ($request->user()?->hasVerifiedEmail() && $request->user()?->id === $course->instructor_id), 403);

        return view('marketplace.lesson', compact('course', 'lesson', 'enrolled'));
    }

    public function complete(Request $request, string $slug, int $lesson)
    {
        $course = $this->published($slug);
        abort_unless($this->enrolled($course, $request), 403);
        abort_unless(DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->exists(), 404);
        DB::table('lesson_completions')->insertOrIgnore(['course_lesson_id' => $lesson, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('status', 'Lesson marked complete.');
    }

    public function library(Request $request)
    {
        $courses = Course::whereIn('id', DB::table('course_enrollments')->where('user_id', $request->user()->id)->select('course_id'))->get();
        $progress = [];
        foreach ($courses as $course) {
            $ids = DB::table('course_lessons')->where('course_id', $course->id)->pluck('id');
            $progress[$course->id] = ['total' => $ids->count(), 'done' => DB::table('lesson_completions')->where('user_id', $request->user()->id)->whereIn('course_lesson_id', $ids)->count()];
        }

        return view('marketplace.library', compact('courses', 'progress'));
    }

    private function published(string $slug): Course
    {
        return Course::where('slug', $slug)->where('status', 'active')->where('is_approved', 'approved')->firstOrFail();
    }

    private function enrolled(Course $course, Request $request): bool
    {
        return $request->user()?->hasVerifiedEmail() && DB::table('course_enrollments')->where('course_id', $course->id)->where('user_id', $request->user()->id)->exists();
    }
}
