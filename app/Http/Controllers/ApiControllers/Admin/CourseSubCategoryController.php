<?php

namespace App\Http\Controllers\ApiControllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseSubCategoryRequest;
use App\Models\CourseCategory;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseSubCategoryController extends Controller
{
    use FileUpload;

    /**
     * 📄 عرض قائمة الـ Sub-Categories لفئة رئيسية
     */
    public function index(CourseCategory $courseCategory)
    {
        $categories = CourseCategory::where('parent_id', $courseCategory->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $categories
        ], 200);
    }

    /**
     * 💾 إنشاء Sub-Category جديدة
     */
    public function store(CourseSubCategoryRequest $request, CourseCategory $courseCategory)
    {
        $imagePath = $request->hasFile('image')
            ? $this->uploadFile($request->file('image'), 'category/images')
            : null;

        $iconPath = $request->hasFile('icon')
            ? $this->uploadFile($request->file('icon'), 'category/icons')
            : null;

        $category = CourseCategory::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'image'       => $imagePath,
            'icon'        => $iconPath,
            'parent_id'   => $courseCategory->id,
            'is_trending' => $request->is_trending ?? 0,
            'status'      => $request->status ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-category created successfully.',
            'data'    => $category
        ], 201);
    }

    /**
     * 🔍 عرض بيانات Sub-Category
     */
    public function show(CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        return response()->json([
            'success' => true,
            'data'    => $course_sub_category
        ], 200);
    }

    /**
     * 🔄 تحديث Sub-Category
     */
    public function update(Request $request, CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        $category = $course_sub_category;

        if ($request->hasFile('image')) {
            $this->deleteFile($category->image);
            $category->image = $this->uploadFile($request->file('image'), 'category/images');
        }

        if ($request->hasFile('icon')) {
            $this->deleteFile($category->icon);
            $category->icon = $this->uploadFile($request->file('icon'), 'category/icons');
        }

        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'is_trending' => $request->is_trending ?? 0,
            'status'      => $request->status ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-category updated successfully.',
            'data'    => $category
        ], 200);
    }

    /**
     * 🗑 حذف Sub-Category
     */
    public function destroy(CourseCategory $courseCategory, CourseCategory $course_sub_category)
    {
        $course_sub_category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sub-category deleted successfully.'
        ], 200);
    }
}
