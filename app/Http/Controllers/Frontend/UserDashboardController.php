<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        return app(LearningController::class)->library($request);
    }

    public function becomeInstructor()
    {
        return view('frontend.student-dashboard.become-instructor');
    }
}
