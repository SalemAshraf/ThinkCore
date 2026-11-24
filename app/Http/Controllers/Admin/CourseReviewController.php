<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseReviewController extends Controller
{
    public function index()
    {
        $courses = Course::where('is_approved', 'pending')->where('status', 'inactive')->latest()->get();

        return view('marketplace.review', compact('courses'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected']);
        abort_unless($course->status === 'inactive' && $course->is_approved === 'pending', 409);
        if ($data['decision'] === 'approved') {
            abort_unless($course->description && $course->price !== null && DB::table('course_lessons')->where('course_id', $course->id)->exists(), 422);
        }
        $course->is_approved = $data['decision'];
        $course->status = $data['decision'] === 'approved' ? 'active' : 'draft';
        $course->save();

        return back()->with('status', 'Review saved.');
    }
}
