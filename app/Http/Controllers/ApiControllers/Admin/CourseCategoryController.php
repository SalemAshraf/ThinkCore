<?php

namespace App\Http\Controllers\ApiControllers\Admin;

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
     * 📄 عرض قائمة الفئات الرئيسية
     */
    public function index()
    {
        $categories = CourseCategory::whereNull('parent_id')->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $categories
        ], 200);
    }

    /**
     * 💾 إنشاء فئة رئيسية جديدة
     */
    public function store(CourseCategoryRequest $request)
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
            'is_trending' => $request->is_trending ?? 0,
            'status'      => $request->status ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category
        ], 201);
    }

    /**
     * 🔍 عرض بيانات فئة رئيسية
     */
    public function show(CourseCategory $course_category)
    {
        return response()->json([
            'success' => true,
            'data'    => $course_category
        ], 200);
    }

    /**
     * 🔄 تحديث بيانات فئة رئيسية
     */
    public function update(CourseCategoryUpdateRequest $request, CourseCategory $course_category)
    {
        if ($request->hasFile('image')) {
            $this->deleteFile($course_category->image);
            $course_category->image = $this->uploadFile($request->file('image'), 'category/images');
        }

        if ($request->hasFile('icon')) {
            $this->deleteFile($course_category->icon);
            $course_category->icon = $this->uploadFile($request->file('icon'), 'category/icons');
        }

        $course_category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'is_trending' => $request->is_trending ?? 0,
            'status'      => $request->status ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data'    => $course_category
        ], 200);
    }

    /**
     * 🗑 حذف فئة رئيسية
     */
    public function destroy(CourseCategory $course_category)
    {
        if (CourseCategory::where('parent_id', $course_category->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category has subcategories and cannot be deleted.'
            ], 400);
        }

        try {
            if ($course_category->image) {
                $this->deleteFile($course_category->image);
            }
            if ($course_category->icon) {
                $this->deleteFile($course_category->icon);
            }

            $course_category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the category.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
