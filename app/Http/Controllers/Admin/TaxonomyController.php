<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLanguage;
use App\Models\CourseLevel;
use App\Traits\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaxonomyController extends Controller
{
    use FileUpload;

    private function context(Request $request): array
    {
        $route = $request->route()->getName();
        $kind = str_contains($route, 'sub-categories') ? 'sub-categories' : (str_contains($route, 'categories') ? 'categories' : (str_contains($route, 'languages') ? 'languages' : 'levels'));
        $model = match ($kind) {'categories', 'sub-categories' => CourseCategory::class, 'languages' => CourseLanguage::class, default => CourseLevel::class};
        $parent = $kind === 'sub-categories' ? CourseCategory::whereNull('parent_id')->findOrFail($request->route('course_category')) : null;
        $prefix = 'admin.course-'.$kind;
        $title = match ($kind) {'categories'=>'التصنيفات', 'sub-categories'=>'التصنيفات الفرعية', 'languages'=>'اللغات', default=>'المستويات'};
        return compact('kind','model','parent','prefix','title');
    }

    private function item(Request $request, array $context)
    {
        $parameter = match ($context['kind']) {'categories'=>'course_category', 'sub-categories'=>'course_sub_category', 'languages'=>'course_language', default=>'course_level'};
        $query = $context['model']::query();
        if ($context['kind'] === 'sub-categories') $query->where('parent_id', $context['parent']->id);
        if ($context['kind'] === 'categories') $query->whereNull('parent_id');
        return $query->findOrFail($request->route($parameter));
    }

    public function index(Request $request)
    {
        $context = $this->context($request);
        $query = $context['model']::query();
        if (in_array($context['kind'], ['categories','sub-categories'])) $query->where('parent_id', $context['parent']?->id);
        $items = $query->orderBy('name')->paginate(15);
        return view('admin.taxonomy.index', $context + compact('items'));
    }

    public function create(Request $request) { return view('admin.taxonomy.form', $this->context($request)); }
    public function edit(Request $request)
    {
        $context = $this->context($request);
        return view('admin.taxonomy.form', $context + ['item'=>$this->item($request,$context)]);
    }
    public function show(Request $request) { abort(404); }
    public function store(Request $request) { return $this->save($request, false); }
    public function update(Request $request) { return $this->save($request, true); }

    private function save(Request $request, bool $editing)
    {
        $context = $this->context($request);
        $item = $editing ? $this->item($request,$context) : new $context['model'];
        $table = $item->getTable();
        $data = $request->validate(['name'=>['required','string','max:255',Rule::unique($table,'name')->ignore($item->id)],'status'=>'nullable|boolean','is_trending'=>'nullable|boolean','image'=>'nullable|image|mimes:jpg,jpeg,png,gif|max:2048','icon'=>'nullable|image|mimes:jpg,jpeg,png,gif|max:2048']);
        $newFiles = [];
        $oldFiles = [];
        try {
            DB::transaction(function () use ($request, $context, $item, $data, &$newFiles, &$oldFiles) {
                if ($context['parent']) {
                    $parent = CourseCategory::whereKey($context['parent']->id)->whereNull('parent_id')->lockForUpdate()->first();
                    abort_unless($parent, 409, 'Parent category no longer exists.');
                }
                if ($item->exists) $item = $context['model']::whereKey($item->id)->lockForUpdate()->firstOrFail();
                $item->name = $data['name'];
                if (!$item->exists) $item->slug = (Str::slug($data['name']) ?: 'topic').'-'.Str::lower(Str::random(10));
                if (in_array($context['kind'], ['categories','sub-categories'])) {
                    $item->parent_id = $context['parent']?->id;
                    $item->status = $request->boolean('status');
                    $item->is_trending = $request->boolean('is_trending');
                    foreach (['image','icon'] as $field) {
                        if ($request->hasFile($field)) {
                            $oldFiles[] = $item->$field;
                            $item->$field = $this->uploadFile($request->file($field), 'category/'.$field);
                            $newFiles[] = $item->$field;
                        }
                    }
                }
                $item->save();
            });
        } catch (\Throwable $exception) {
            foreach ($newFiles as $file) $this->deleteFile($file);
            throw $exception;
        }
        DB::afterCommit(function () use ($oldFiles) {
            foreach ($oldFiles as $file) {
                try { $this->deleteFile($file); } catch (\Throwable $cleanup) { report($cleanup); }
            }
        });
        return redirect()->route($context['prefix'].'.index', $context['parent'] ? [$context['parent']->id] : [])->with('status','تم حفظ البيانات.');
    }

    public function destroy(Request $request)
    {
        $context = $this->context($request);
        $deleted = DB::transaction(function () use ($request, $context) {
            // Child writes lock parent first too, preventing create/delete races.
            if ($context['parent']) CourseCategory::whereKey($context['parent']->id)->lockForUpdate()->firstOrFail();
            $item = $this->item($request,$context);
            $item = $context['model']::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $column = match ($context['kind']) {'categories','sub-categories'=>'category_id','languages'=>'course_language_id',default=>'course_level_id'};
            // Locking reads see current committed data on MySQL, not an older snapshot.
            $inUse = Course::where($column,$item->id)->lockForUpdate()->first(['id']);
            $hasChildren = in_array($context['kind'],['categories','sub-categories']) && CourseCategory::where('parent_id',$item->id)->lockForUpdate()->first(['id']);
            if ($inUse || $hasChildren) return false;
            $item->delete();
            DB::afterCommit(function () use ($item) {
                foreach (['image','icon'] as $field) {
                    try { $this->deleteFile($item->$field); } catch (\Throwable $cleanup) { report($cleanup); }
                }
            });
            return true;
        }, 5);
        if (!$deleted) return back()->withErrors(['delete'=>'لا يمكن حذف عنصر مستخدم في كورسات أو مرتبط بتصنيفات فرعية.']);
        return back()->with('status','تم حذف العنصر غير المستخدم.');
    }
}
