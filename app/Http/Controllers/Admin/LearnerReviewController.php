<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearnerReviewController extends Controller
{
    public function index()
    {
        $reviews = DB::table('course_reviews')->join('courses', 'courses.id', '=', 'course_reviews.course_id')->where('course_reviews.status', 'pending')->orderBy('course_reviews.id')->select('course_reviews.*', 'courses.title as course_title')->paginate(20);

        return view('marketplace.review-moderation', compact('reviews'));
    }

    public function update(Request $request, int $review)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'revision' => 'required|integer|min:1']);
        $count = DB::table('course_reviews')->where('id', $review)->where('status', 'pending')->where('revision', $data['revision'])->update(['status' => $data['decision'], 'updated_at' => now()]);
        abort_unless($count, 409);

        return back()->with('status', 'تم حفظ قرار المراجعة.');
    }
}
