<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstructorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $courses = \App\Models\Course::where('instructor_id', $request->user()->id)->latest()->get();
        $enrollments = \Illuminate\Support\Facades\DB::table('course_enrollments')->whereIn('course_id', $courses->pluck('id'))->count();
        return view('marketplace.instructor-dashboard', compact('courses', 'enrollments'));
    }
}
