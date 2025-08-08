<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseCategoryRequest;
use App\Http\Requests\Admin\CourseCategoryUpdateRequest;
use App\Models\CourseCategory;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseCategoryController extends Controller
{
    use FileUpload;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = CourseCategory::where('parent_id', null)->paginate(5);
        return view('admin.course.course-category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.course.course-category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseCategoryRequest $request)
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
    public function edit(string $id)
    {
        $category = CourseCategory::findOrFail($id);
        return view('admin.course.course-category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseCategoryUpdateRequest $request, CourseCategory $course_category)
    {
        $category = $course_category;

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
    public function destroy(CourseCategory $course_category)
    {
        if (CourseCategory::where('parent_id', $course_category->id)->exists()) {
            noty()->error('This category has subcategories and cannot be deleted.');
            return redirect()->back();
        }
        try {
            if ($course_category->image) {
                $this->deleteFile($course_category->image);
            }
            if ($course_category->icon) {
                $this->deleteFile($course_category->icon);
            }
            $course_category->delete();
            noty()->error('Category deleted successfully.');
            return redirect()->route('admin.course-categories.index');
        } catch (\Exception $e) {
            noty()->error('An error occurred while deleting the category.');
            return redirect()->back();
        }
    }
}
