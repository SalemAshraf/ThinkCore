<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumController extends Controller
{
    public function index(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        $lessons = DB::table('course_lessons')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get();

        return view('marketplace.curriculum', compact('course', 'lessons'));
    }

    public function store(Request $request, int $course)
    {
        $course = $this->owned($request, $course);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string|max:100000', 'position' => 'required|integer|min:1|max:10000', 'is_preview' => 'nullable|boolean']);
        DB::transaction(function () use ($course, $data, $request) {
            DB::table('course_lessons')->insert(['course_id' => $course->id, 'title' => $data['title'], 'content' => $data['content'], 'position' => $data['position'], 'is_preview' => $request->boolean('is_preview'), 'created_at' => now(), 'updated_at' => now()]);
            // Content changes must be reviewed again before remaining public.
            $course->status = 'draft';
            $course->is_approved = 'pending';
            $course->save();
        });

        return back()->with('status', 'Lesson saved. Course is a draft pending review.');
    }

    public function update(Request $request, int $course, int $lesson)
    {
        $course = $this->owned($request, $course);
        abort_unless(DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->exists(), 404);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string|max:100000', 'position' => 'required|integer|min:1|max:10000', 'is_preview' => 'nullable|boolean']);
        DB::transaction(function () use ($course, $lesson, $data, $request) {
            DB::table('course_lessons')->where('course_id', $course->id)->where('id', $lesson)->update(['title' => $data['title'], 'content' => $data['content'], 'position' => $data['position'], 'is_preview' => $request->boolean('is_preview'), 'updated_at' => now()]);
            $course->status = 'draft';
            $course->is_approved = 'pending';
            $course->save();
        });

        return back()->with('status', 'Lesson updated. Review required before publication.');
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

        return back()->with('status', 'Lesson removed. Review required before publication.');
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

    private function owned(Request $request, int $id): Course
    {
        return Course::where('instructor_id', $request->user()->id)->findOrFail($id);
    }
}
