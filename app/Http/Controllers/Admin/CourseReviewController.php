<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\CourseReviewSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseReviewController extends Controller
{
    public function index()
    {
        $courses = DB::transaction(function () {
            return Course::where('is_approved', 'pending')->where('status', 'inactive')->latest()->lockForUpdate()->get()->map(function ($course) {
                return ['course' => $course, 'token' => CourseReviewSnapshot::token($course), 'lessons' => DB::table('course_lessons')->where('course_id', $course->id)->orderBy('position')->orderBy('id')->get()];
            });
        });

        return view('marketplace.review', compact('courses'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'snapshot' => 'required|string|size:64']);
        DB::transaction(function () use ($data, $course) {
            $course = Course::whereKey($course->id)->lockForUpdate()->firstOrFail();
            abort_unless($course->status === 'inactive' && $course->is_approved === 'pending', 409);
            abort_unless(hash_equals(CourseReviewSnapshot::token($course), $data['snapshot']), 409, 'Course changed. Reload and review the current content before deciding.');
            if ($data['decision'] === 'approved') {
                abort_unless($course->description && $course->price !== null && DB::table('course_lessons')->where('course_id', $course->id)->exists(), 422);
            }
            $course->is_approved = $data['decision'];
            $course->status = $data['decision'] === 'approved' ? 'active' : 'draft';
            $course->save();
        });

        return back()->with('status', 'تم حفظ قرار مراجعة الكورس.');
    }
}
