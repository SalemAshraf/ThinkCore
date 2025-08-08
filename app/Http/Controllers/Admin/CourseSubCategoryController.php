<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseCategoryRequest;
use App\Http\Requests\Admin\CourseCategoryUpdateRequest;
use App\Http\Requests\Admin\CourseSubCategoryRequest;
use App\Models\CourseCategory;
use App\Models\CourseLevel;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class CourseSubCategoryController extends Controller
{
    use FileUpload;
    /**
     * Display a listing of the resource.
     */
    public function index(CourseCategory $courseCategory)
    {
        $categories = CourseCategory::where('parent_id', $courseCategory->id)->paginate(5);
        return view('admin.course.course-sub-category.index', compact('courseCategory', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CourseCategory $courseCategory)
    {
        return view('admin.course.course-sub-category.create', compact('courseCategory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseSubCategoryRequest $request, CourseCategory $courseCategory)
    {
        $imagepath = null;
        $iconpath = null;
        if ($request->hasFile('image')) {
            $imagepath = $this->uploadFile($request->file('image'), 'category/images');
        }
        if ($request->hasFile('icon')) {
            $iconpath = $this->uploadFile($request->file('icon'), 'category/icons');
        }
        $category = new CourseCategory();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->image = $imagepath;
        $category->icon = $iconpath;
        $category->parent_id = $courseCategory->id;
        $category->is_trending = $request->is_trending ?? 0;
        $category->status = $request->status ?? 0;
        $category->save();
        noty()->success('Category added successfully.');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        return view('admin.course.course-sub-category.edit', compact('course_sub_category', 'courseCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        $category = $course_sub_category;

    if ($request->hasFile('image')) {
        if ($category->image) {
            $this->deleteFile($category->image);
        }
        $category->image = $this->uploadFile($request->file('image'), 'category/images');
    }

    if ($request->hasFile('icon')) {
        if ($category->icon) {
            $this->deleteFile($category->icon);
        }
        $category->icon = $this->uploadFile($request->file('icon'), 'category/icons');
    }

    $category->name = $request->name;
    $category->slug = Str::slug($request->name);
    $category->is_trending = $request->is_trending ?? 0;
    $category->status = $request->status ?? 0;
    $category->save();

    noty()->success('Category updated successfully.');
    return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        $course_sub_category->delete();

        noty()->error('Sub Category deleted successfully.');

        return redirect()->back();
    }
}
