<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CourseLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $levels = CourseLevel::query()->paginate(5);
        return view('admin.course.course-level.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.course.course-level.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_languages'
        ]);

        CourseLevel::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name'))
        ])->save();

        noty()->success('Level added successfully.');

        return redirect()->route('admin.course-levels.index');
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
        $level = CourseLevel::findOrFail($id);
        return view('admin.course.course-level.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_levels,name,' . $id
        ]);

        $level = CourseLevel::findOrFail($id);
        $level->name = $request->input('name');
        $level->slug = Str::slug($request->input('name'));
        $level->save();

        noty()->success('Level updated successfully.');

        return redirect()->route('admin.course-levels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $level = CourseLevel::findOrFail($id);
        $level->delete();

        noty()->error('Level deleted successfully.');

        return redirect()->route('admin.course-levels.index');
    }
}
