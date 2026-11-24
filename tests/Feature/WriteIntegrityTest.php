<?php

use App\Models\Course;
use App\Support\TaxonomyReferences;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('failed course image replacement keeps original file and removes the uncommitted upload', function () {
    Storage::fake('public');
    Storage::disk('public')->put('thumbnail/original.png','original bytes');
    $owner=instructor();$course=draftFor($owner);$course->thumbnail='/storage/thumbnail/original.png';$course->save();
    Course::updating(function () { throw new RuntimeException('Simulated failed database write'); });
    try {
        $this->actingAs($owner)->post('/instructor/courses/moreinfo',[
            'id'=>$course->id,'current_step'=>1,'title'=>'Replacement','description'=>'Text','price'=>0,
            'thumbnail'=>UploadedFile::fake()->image('replacement.png'),
        ])->assertServerError();
    } finally {
        Course::flushEventListeners();
    }
    expect($course->fresh()->thumbnail)->toBe('/storage/thumbnail/original.png');
    expect(Storage::disk('public')->allFiles('thumbnail'))->toBe(['thumbnail/original.png']);
    expect(Storage::disk('public')->get('thumbnail/original.png'))->toBe('original bytes');
});

test('failed course creation removes its new image without persisting a draft', function () {
    Storage::fake('public');
    Course::creating(function () { throw new RuntimeException('Simulated failed insert'); });
    try {
        $this->actingAs(instructor())->post('/instructor/courses/store',[
            'title'=>'New draft','description'=>'Text','price'=>0,'thumbnail'=>UploadedFile::fake()->image('new.png'),
        ])->assertServerError();
    } finally {
        Course::flushEventListeners();
    }
    expect(Course::count())->toBe(0);
    expect(Storage::disk('public')->allFiles('thumbnail'))->toBe([]);
});

test('taxonomy references revalidate deleted and inactive rows inside the transaction', function () {
    $category=categoryRecord('Inactive');$category->status=0;$category->save();
    expect(fn () => DB::transaction(fn () => TaxonomyReferences::lock(['category'=>$category->id])))->toThrow(ValidationException::class);
    foreach (['category','level','language'] as $field) {
        expect(fn () => DB::transaction(fn () => TaxonomyReferences::lock([$field=>999])))->toThrow(ValidationException::class);
    }
});

test('course metadata assigns existing taxonomy without breaking parent or reference deletion guards', function () {
    $owner=instructor();$course=draftFor($owner);$parent=categoryRecord('Parent assignment');$child=categoryRecord('Child assignment',$parent->id);
    $level=App\Models\CourseLevel::create(['name'=>'Test level','slug'=>'test-level']);
    $language=App\Models\CourseLanguage::create(['name'=>'Test language','slug'=>'test-language']);
    $this->actingAs($owner)->postJson('/instructor/courses/moreinfo',[
        'id'=>$course->id,'current_step'=>2,'category'=>$child->id,'level'=>$level->id,'language'=>$language->id,
    ])->assertOk();
    expect($course->fresh()->category_id)->toBe($child->id);
    $this->actingAs(taxonomyAdmin(),'admin');
    $this->delete('/admin/course-categories/'.$parent->id.'/sub-categories/'.$child->id)->assertSessionHasErrors('delete');
    $this->delete('/admin/course-levels/'.$level->id)->assertSessionHasErrors('delete');
    $this->delete('/admin/course-languages/'.$language->id)->assertSessionHasErrors('delete');
});

test('successful thumbnail replacement commits a usable file before retiring the old file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('thumbnail/old.png','old');
    $owner=instructor();$course=draftFor($owner);$course->thumbnail='/storage/thumbnail/old.png';$course->save();
    $this->actingAs($owner)->postJson('/instructor/courses/moreinfo',[
        'id'=>$course->id,'current_step'=>1,'title'=>'Saved replacement','description'=>'Text','price'=>0,
        'thumbnail'=>UploadedFile::fake()->image('replacement.png'),
    ])->assertOk();
    $path=$course->fresh()->thumbnail;
    expect($path)->not->toBe('/storage/thumbnail/old.png');
    Storage::disk('public')->assertExists(substr($path,9));
    Storage::disk('public')->assertMissing('thumbnail/old.png');
});
