<?php

namespace App\Support;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseReviewSnapshot
{
    public static function token(Course $course): string
    {
        $sections = DB::table('course_sections')->where('course_id', $course->id)->orderBy('id')->get();
        $lessons = DB::table('course_lessons')->where('course_id', $course->id)->orderBy('id')->get();

        // Includes all stored course metadata plus lesson and section versions.
        return hash('sha256', json_encode([$course->getAttributes(), $sections, $lessons], JSON_THROW_ON_ERROR));
    }
}
