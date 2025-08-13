<?php

namespace App\Http\Controllers\ApiControllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseLevelController extends Controller
{
    /**
     * 📄 عرض جميع المستويات مع التصفح (Pagination)
     */
    public function index()
    {
        $levels = CourseLevel::paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $levels
        ], 200);
    }

    /**
     * 💾 إنشاء مستوى جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_levels'
        ]);

        $level = CourseLevel::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level created successfully.',
            'data'    => $level
        ], 201);
    }

    /**
     * 🔍 عرض مستوى محدد
     */
    public function show($id)
    {
        $level = CourseLevel::find($id);

        if (!$level) {
            return response()->json([
                'success' => false,
                'message' => 'Level not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $level
        ], 200);
    }

    /**
     * 🔄 تحديث مستوى
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_levels,name,' . $id
        ]);

        $level = CourseLevel::find($id);

        if (!$level) {
            return response()->json([
                'success' => false,
                'message' => 'Level not found.'
            ], 404);
        }

        $level->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level updated successfully.',
            'data'    => $level
        ], 200);
    }

    /**
     * 🗑 حذف مستوى
     */
    public function destroy($id)
    {
        $level = CourseLevel::find($id);

        if (!$level) {
            return response()->json([
                'success' => false,
                'message' => 'Level not found.'
            ], 404);
        }

        $level->delete();

        return response()->json([
            'success' => true,
            'message' => 'Level deleted successfully.'
        ], 200);
    }
}
