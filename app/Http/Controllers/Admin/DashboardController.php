<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{public function index()
    {
        $stats = [
            'learners' => \App\Models\User::where('role','student')->count(),
            'instructors' => \App\Models\User::where('role','instructor')->count(),
            'published' => \App\Models\Course::where('status','active')->where('is_approved','approved')->count(),
            'enrollments' => \Illuminate\Support\Facades\DB::table('course_enrollments')->count(),
            'applications' => \App\Models\User::where('approved_status','pending')->whereNotNull('document')->count(),
            'courses' => \App\Models\Course::where('status','inactive')->where('is_approved','pending')->count(),
            'reviews' => \Illuminate\Support\Facades\DB::table('course_reviews')->where('status','pending')->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
