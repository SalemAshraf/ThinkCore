<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    public function index(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        $lessons = DB::table('course_lessons')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get();

        $sections = DB::table('course_sections')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get();

        return view('marketplace.curriculum', compact('course', 'lessons', 'sections'));
    }

    public function store(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string|max:100000', 'position' => 'required|integer|min:1|max:10000', 'is_preview' => 'nullable|boolean', 'section_id' => ['nullable', 'integer', Rule::exists('course_sections', 'id')->where('course_id', $course->id)]]);
        DB::transaction(function () use ($course, $data, $request) {
            DB::table('course_lessons')->insert(['course_id' => $course->id, 'title' => $data['title'], 'content' => $data['content'], 'position' => $data['position'], 'section_id' => $data['section_id'] ?? null, 'is_preview' => $request->boolean('is_preview'), 'created_at' => now(), 'updated_at' => now()]);
            // Content changes must be reviewed again before remaining public.
            $course->status = 'draft';
            $course->is_approved = 'pending';
            $course->save();
        });

        return back()->with('status', 'تم حفظ الدرس. الكورس مسودة تحتاج مراجعة قبل النشر.');
    }

    public function update(Request $request, int $course, int $lesson)
    {
        $course = $this->owned($request, $course);
        abort_unless(DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->exists(), 404);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string|max:100000', 'position' => 'required|integer|min:1|max:10000', 'is_preview' => 'nullable|boolean', 'section_id' => ['nullable', 'integer', Rule::exists('course_sections', 'id')->where('course_id', $course->id)]]);
        DB::transaction(function () use ($course, $lesson, $data, $request) {
            DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->update(['title' => $data['title'], 'content' => $data['content'], 'position' => $data['position'], 'section_id' => $data['section_id'] ?? null, 'is_preview' => $request->boolean('is_preview'), 'updated_at' => now()]);
            $course->status = 'draft';
            $course->is_approved = 'pending';
            $course->save();
        });

        return back()->with('status', 'تم تعديل الدرس. الكورس يحتاج مراجعة قبل النشر.');
    }

    public function destroy(Request $request, int $course, int $lesson)
    {
        $course = $this->owned($request, $course);
        abort_unless(DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->exists(), 404);
        DB::transaction(function () use ($course, $lesson) {
            DB::table('lesson_completions')->where('course_lesson_id', $lesson)->delete();
            DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->delete();
            $course->status = 'draft';
            $course->is_approved = 'pending';
            $course->save();
        });

        return back()->with('status', 'تم حذف الدرس وإعادة الكورس للمراجعة.');
    }

    public function submit(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        abort_unless($course->description && $course->price !== null && DB::table('course_lessons')->where('course_id', $course->id)->exists(), 422, 'Add description, price and at least one lesson before review.');
        $course->status = 'inactive';
        $course->is_approved = 'pending';
        $course->save();

        return back()->with('status', 'Submitted for administrator review.');
    }

    public function storeSection(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        $data = $request->validate(['title' => 'required|string|max:255', 'position' => 'required|integer|min:1|max:10000']);
        DB::transaction(function () use ($course, $data) {
            DB::table('course_sections')->insert($data + ['course_id' => $course->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->unpublish($course);
        });

        return back()->with('status', 'تمت إضافة القسم. الكورس يحتاج مراجعة قبل النشر.');
    }

    public function updateSection(Request $request, int $course, int $section)
    {
        $course = $this->owned($request, $course);
        abort_unless(DB::table('course_sections')->where('course_id', $course->id)->where('id', $section)->exists(), 404);
        $data = $request->validate(['title' => 'required|string|max:255', 'position' => 'required|integer|min:1|max:10000']);
        DB::transaction(function () use ($course, $section, $data) {
            DB::table('course_sections')->where('course_id', $course->id)->where('id', $section)->update($data + ['updated_at' => now()]);
            $this->unpublish($course);
        });

        return back()->with('status', 'تم تحديث القسم وإعادة الكورس للمراجعة.');
    }

    public function destroySection(Request $request, int $course, int $section)
    {
        $course = $this->owned($request, $course);
        abort_unless(DB::table('course_sections')->where('course_id', $course->id)->where('id', $section)->exists(), 404);
        // Never cascade-delete lessons when removing a section.
        DB::transaction(function () use ($course, $section) {
            DB::table('course_lessons')->where('course_id', $course->id)->where('section_id', $section)->update(['section_id' => null]);
            DB::table('course_sections')->where('course_id', $course->id)->where('id', $section)->delete();
            $this->unpublish($course);
        });

        return back()->with('status', 'تم حذف القسم مع الاحتفاظ بالدروس بدون قسم.');
    }

    private function unpublish(Course $course): void
    {
        $course->status = 'draft';
        $course->is_approved = 'pending';
        $course->save();
    }

    private function owned(Request $request, int $id): Course
    {
        return Course::where('instructor_id', $request->user()->id)->findOrFail($id);
    }
}
