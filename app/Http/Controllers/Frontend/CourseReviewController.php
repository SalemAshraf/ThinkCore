<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)->where('status', 'active')->where('is_approved', 'approved')->firstOrFail();
        abort_if($course->instructor_id === $request->user()->id, 403);
        abort_unless(DB::table('course_enrollments')->where('course_id', $course->id)->where('user_id', $request->user()->id)->exists(), 403);
        $data = $request->validate(['rating' => 'required|integer|min:1|max:5', 'body' => 'required|string|min:10|max:3000']);
        // One row per learner/course; every edit returns to moderation. Never trust submitted author/status.
        DB::transaction(function () use ($course, $request, $data) {
            DB::table('users')->where('id', $request->user()->id)->lockForUpdate()->first();
            $existing = DB::table('course_reviews')->where('course_id', $course->id)->where('user_id', $request->user()->id)->first();
            if ($existing) {
                DB::table('course_reviews')->where('id', $existing->id)->update($data + ['status' => 'pending', 'revision' => $existing->revision + 1, 'updated_at' => now()]);
            } else {
                DB::table('course_reviews')->insert($data + ['course_id' => $course->id, 'user_id' => $request->user()->id, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        return back()->with('status', 'تم إرسال تقييمك للمراجعة. لن يظهر قبل الموافقة عليه.');
    }
}
