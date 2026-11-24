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
        $filters = $request->validate(['category' => 'nullable|integer|min:1', 'level' => 'nullable|integer|min:1', 'language' => 'nullable|integer|min:1', 'free' => 'nullable|in:1', 'q' => 'nullable|string|max:120']);
        $categories = DB::table('course_categories')->where('status', 1)->orderBy('name')->get(['id', 'name']);
        $levels = DB::table('course_levels')->orderBy('name')->get(['id', 'name']);
        $languages = DB::table('course_languages')->orderBy('name')->get(['id', 'name']);
        $query = Course::query()->leftJoin('users as teacher', 'teacher.id', '=', 'courses.instructor_id')->leftJoin('course_categories as category', 'category.id', '=', 'courses.category_id')->leftJoin('course_levels as level', 'level.id', '=', 'courses.course_level_id')->where('courses.status', 'active')->where('courses.is_approved', 'approved')->select('courses.*', 'teacher.name as instructor_name', 'category.name as category_name', 'level.name as level_name');
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 120);
        if ($term !== '') {
            $query->where('title', 'like', '%'.addcslashes($term, '%_\\').'%');
        }
        foreach (['category' => 'category_id', 'level' => 'course_level_id', 'language' => 'course_language_id'] as $key => $column) {
            if (! empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }
        if (! empty($filters['free'])) {
            $query->where('price', 0)->whereNotNull('price');
        }
        $courses = $query->latest('courses.created_at')->paginate(12)->withQueryString();

        return view('marketplace.catalog', compact('courses', 'term', 'filters', 'categories', 'levels', 'languages'));
    }

    public function show(Request $request, string $slug)
    {
        $course = $this->published($slug);
        $course->instructor_name = DB::table('users')->where('id', $course->instructor_id)->value('name');
        $course->category_name = DB::table('course_categories')->where('id', $course->category_id)->value('name');
        $course->level_name = DB::table('course_levels')->where('id', $course->course_level_id)->value('name');
        $course->language_name = DB::table('course_languages')->where('id', $course->course_language_id)->value('name');
        $isEnrolled = $this->enrolled($course, $request);
        // Never hydrate lesson bodies into a public page.
        $lessons = DB::table('course_lessons')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get(['id', 'title', 'is_preview', 'section_id']);

        $sections = DB::table('course_sections')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get();
        $reviews = DB::table('course_reviews')->where('course_id', $course->id)->where('status', 'approved')->latest('id')->paginate(10);
        $rating = DB::table('course_reviews')->where('course_id', $course->id)->where('status', 'approved')->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
        $canReview = $this->enrolled($course, $request) && $request->user()->id !== $course->instructor_id;
        $ownReview = $request->user() ? DB::table('course_reviews')->where('course_id', $course->id)->where('user_id', $request->user()->id)->first() : null;

        return view('marketplace.course', compact('course', 'lessons', 'sections', 'reviews', 'rating', 'canReview', 'ownReview', 'isEnrolled'));
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

        return redirect()->route('marketplace.course', $course->slug)->with('status', 'تم انضمامك للكورس. رحلتك تبدأ من أول درس.');
    }

    public function lesson(Request $request, string $slug, int $lesson)
    {
        $course = $this->published($slug);
        $lesson = DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->first();
        abort_unless($lesson, 404);
        $enrolled = $this->enrolled($course, $request);
        abort_unless($lesson->is_preview || $enrolled || ($request->user()?->hasVerifiedEmail() && $request->user()?->id === $course->instructor_id), 403);

        $ordered = $this->orderedLessons($course->id);
        $offset = $ordered->search(fn ($item) => $item->id === $lesson->id);
        $mayReadAll = $enrolled || ($request->user()?->hasVerifiedEmail() && $request->user()?->id === $course->instructor_id);
        $previous = $offset > 0 ? $ordered[$offset - 1] : null;
        $next = $offset < $ordered->count() - 1 ? $ordered[$offset + 1] : null;
        if (! $mayReadAll) {
            if ($previous && ! $previous->is_preview) {
                $previous = null;
            }
            if ($next && ! $next->is_preview) {
                $next = null;
            }
        }
        $completed = $enrolled && DB::table('lesson_completions')->where('user_id', $request->user()->id)->where('course_lesson_id', $lesson->id)->exists();

        return view('marketplace.lesson', compact('course', 'lesson', 'enrolled', 'previous', 'next', 'completed', 'ordered', 'mayReadAll'));
    }

    public function complete(Request $request, string $slug, int $lesson)
    {
        $course = $this->published($slug);
        abort_unless($this->enrolled($course, $request), 403);
        abort_unless(DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->exists(), 404);
        DB::table('lesson_completions')->insertOrIgnore(['course_lesson_id' => $lesson, 'user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('status', 'تم تسجيل الدرس كمكتمل. خطوة جديدة في رحلتك.');
    }

    public function library(Request $request)
    {
        $courses = Course::whereIn('id', DB::table('course_enrollments')->where('user_id', $request->user()->id)->select('course_id'))->get();
        $progress = [];
        foreach ($courses as $course) {
            $ordered = $this->orderedLessons($course->id);
            $ids = $ordered->pluck('id');
            $completedIds = DB::table('lesson_completions')->where('user_id', $request->user()->id)->whereIn('course_lesson_id', $ids)->pluck('course_lesson_id');
            $resume = $ordered->first(fn ($item) => ! $completedIds->contains($item->id));
            $progress[$course->id] = ['total' => $ids->count(), 'done' => $completedIds->count(), 'resume' => $resume?->id];
        }

        return view('marketplace.library', compact('courses', 'progress'));
    }

    private function orderedLessons(int $courseId)
    {
        return DB::table('course_lessons as l')
            ->leftJoin('course_sections as s', function ($join) {
                $join->on('s.id', '=', 'l.section_id')->on('s.course_id', '=', 'l.course_id');
            })->where('l.course_id', $courseId)
            ->orderByRaw('CASE WHEN s.id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('s.position')->orderBy('s.id')->orderBy('l.position')->orderBy('l.id')
            ->get(['l.id', 'l.title', 'l.is_preview']);
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
