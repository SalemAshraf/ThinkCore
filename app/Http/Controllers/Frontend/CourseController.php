<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CourseBasicInfoCreateRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    use FileUpload;

    public function index(Request $request)
    {
        $courses = Course::where('instructor_id', $request->user()->id)->latest()->get();

        return view('frontend.instructor-dashboard.course.index', compact('courses'));
    }

    public function createCourse()
    {
        return view('frontend.instructor-dashboard.course.create');
    }

    public function storeBasicInfo(CourseBasicInfoCreateRequest $request)
    {
        $course = new Course;
        $course->instructor_id = $request->user()->id;
        $this->saveBasicInfo($request, $course);

        return $this->nextStep($course, 2);
    }

    public function edit(Request $request)
    {
        $course = $this->ownedCourse($request);
        $step = (int) $request->input('step', 1);
        abort_unless(in_array($step, [1, 2], true), 404);
        if ($step === 1) {
            return view('frontend.instructor-dashboard.course.edit', compact('course'));
        }
        $categories = CourseCategory::with('subCategories')->where('status', 1)->get();
        $levels = CourseLevel::all();
        $Languages = CourseLanguage::all();

        return view('frontend.instructor-dashboard.course.moreinfo', compact('categories', 'levels', 'Languages', 'course'));
    }

    public function moreinfo(Request $request)
    {
        // Resolve ownership before validation or file operations. Foreign IDs are never exposed.
        $course = $this->ownedCourse($request);
        $request->validate(['current_step' => ['required', Rule::in([1, 2])]]);
        if ((int) $request->current_step === 1) {
            $request->validate((new CourseBasicInfoCreateRequest)->rules());
            $this->saveBasicInfo($request, $course);

            return $this->nextStep($course, 2);
        }
        $data = $request->validate([
            'capacity' => ['nullable', 'integer', 'min:1'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'qna' => ['nullable', 'boolean'],
            'certificate' => ['nullable', 'boolean'],
            'category' => ['nullable', 'integer', Rule::exists('course_categories', 'id')->where('status', 1)],
            'level' => ['nullable', 'integer', 'exists:course_levels,id'],
            'language' => ['nullable', 'integer', 'exists:course_languages,id'],
        ]);
        $course->capacity = $data['capacity'] ?? null;
        $course->duration = $data['duration'] ?? null;
        $course->qna = $request->boolean('qna');
        $course->certificate = $request->boolean('certificate');
        $course->category_id = $data['category'] ?? null;
        $course->course_level_id = $data['level'] ?? null;
        $course->course_language_id = $data['language'] ?? null;
        $course->save();

        return response()->json(['status' => 'Done', 'redirect' => route('instructor.courses')]);
    }

    private function ownedCourse(Request $request): Course
    {
        return Course::where('instructor_id', $request->user()->id)->findOrFail($request->route('id') ?? $request->input('id'));
    }

    private function saveBasicInfo(Request $request, Course $course): void
    {
        foreach (['title', 'price', 'discount', 'seo_description', 'description'] as $field) {
            $course->$field = $request->input($field);
        }
        $course->discount = $request->input('discount') ?? 0;
        // Keep URLs stable on edit and support non-Latin and duplicate course titles.
        if (! $course->exists) {
            $course->slug = (Str::slug($request->title) ?: 'course').'-'.Str::lower(Str::random(12));
        }
        if ($request->hasFile('thumbnail')) {
            $old = $course->thumbnail;
            $course->thumbnail = $this->uploadFile($request->file('thumbnail'), 'thumbnail');
            $this->deleteFile($old);
        }
        $course->demo_video = $request->input('demo_video') ?: null;
        $course->demo_video_source = $request->input('demo_video_source') ?: $request->input('url');
        $course->save();
    }

    private function nextStep(Course $course, int $step)
    {
        return response()->json(['status' => 'Done', 'redirect' => route('instructor.courses.edit', ['id' => $course->id, 'step' => $step])]);
    }
}
