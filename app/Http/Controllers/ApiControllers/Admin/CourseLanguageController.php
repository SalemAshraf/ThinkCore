<?php


namespace App\Http\Controllers\ApiControllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseLanguageController extends Controller
{
    /**
     * 📄 عرض جميع اللغات مع التصفح (Pagination)
     */

    public function index()
    {
        $languages = CourseLanguage::paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $languages
        ], 200);
    }

    /**
     * 💾 إنشاء لغة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_languages'
        ]);

        $language = CourseLanguage::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language created successfully.',
            'data'    => $language
        ], 201);
    }

    /**
     * 🔍 عرض لغة محددة
     */
    public function show($id)
    {
        $language = CourseLanguage::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $language
        ], 200);
    }

    /**
     * 🔄 تحديث لغة
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_languages,name,' . $id
        ]);

        $language = CourseLanguage::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found.'
            ], 404);
        }

        $language->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully.',
            'data'    => $language
        ], 200);
    }

    /**
     * 🗑 حذف لغة
     */
    public function destroy($id)
    {
        $language = CourseLanguage::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found.'
            ], 404);
        }

        $language->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully.'
        ], 200);
    }
}
