<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CourseBasicInfoCreateRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;
use App\Traits\FileUpload;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CourseController extends Controller
{
    use FileUpload;
    public function index()
    {
        return view('frontend.instructor-dashboard.course.index');
    }
    public function createCourse()
    {
        return view('frontend.instructor-dashboard.course.create');
    }

    public function storeBasicInfo(CourseBasicInfoCreateRequest $request)
    {
        $imagepath = null;
        if ($request->hasFile('thumbnail')) {
            $imagepath = $this->uploadFile($request->file('thumbnail'), 'thumbnail');
        }
        $course = new Course();
        $course->title = $request->title;
        $course->price = $request->price;
        $course->discount = $request->discount;
        $course->slug = Str::slug($request->title);
        $course->seo_description = $request->seo_description;
        $course->description = $request->description;
        $course->thumbnail = $imagepath;
        $course->demo_video = $request->demo_video;
        $course->demo_video_source = $request->demo_video_source;
        $course->instructor_id = Auth::guard('web')->user()->id;

        $course->save();

        Session::put('course_create_id', $course->id);
        return response([
            'status' => 'Done',
            'redirect' => route('instructor.courses.edit', ['id' => $course->id, 'step' => $request->next_step])
        ]);
    }

    public function edit(Request $request)
    {

        switch ($request->step) {
            case '1':
                break;
            case '2':
                $categories = CourseCategory::where('status', 1)->get();
                $levels = CourseLevel::query()->get();
                $Languages = CourseLanguage::query()->get();
                return view('frontend.instructor-dashboard.course.moreinfo', compact('categories', 'levels', 'Languages'));
                break;
            default:
                break;
        }
    }

    public function moreinfo(Request $request)
    {
        switch ($request->current_step) {
            case '1':
                break;
            case '2':
                $course = Course::findOrFail($request->id);
                $course->capacity = $request->capacity;
                $course->duration = $request->duration;
                $course->qna = $request->qna ? 1 : 0;
                $course->certificate = $request->certificate ? 1 : 0;
                $course->category_id = $request->category;
                $course->course_level_id = $request->level;
                $course->course_language_id = $request->language;
                $course->save();
                return response([
                    'status' => 'Done',
                    'redirect' => route('instructor.courses.edit', ['id' => $course->id, 'step' => $request->next_step])
                ]);
                break;
            default:
                break;
        }
    }
}
