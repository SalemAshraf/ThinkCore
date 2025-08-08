<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseLanguage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseLanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = CourseLanguage::query()->paginate(5);
        return view('admin.course.course-language.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.course.course-language.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_languages'
        ]);

        CourseLanguage::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name'))
        ])->save();

        noty()->success('Language added successfully.');

        return redirect()->route('admin.course-languages.index');
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
        $language = CourseLanguage::findOrFail($id);
        return view('admin.course.course-language.edit', compact('language'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_languages,name,' . $id
        ]);

        $language = CourseLanguage::findOrFail($id);
        $language->name = $request->input('name');
        $language->slug = Str::slug($request->input('name'));
        $language->save();

        noty()->success('Language updated successfully.');

        return redirect()->route('admin.course-languages.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $language = CourseLanguage::findOrFail($id);
        $language->delete();

        noty()->error('Language deleted successfully.');

        return redirect()->route('admin.course-languages.index');
    }
}
