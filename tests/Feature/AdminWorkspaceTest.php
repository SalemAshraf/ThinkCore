<?php

use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseLevel;
use App\Models\CourseLanguage;
use App\Models\User;
use App\Support\InstructorReviewSnapshot;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function taxonomyAdmin(): Admin { return Admin::create(['name'=>'Admin reviewer','email'=>fake()->unique()->safeEmail(),'password'=>'password']); }
function categoryRecord(string $name, ?int $parent = null): CourseCategory {
    $category = new CourseCategory;
    $category->name=$name; $category->slug=fake()->uuid(); $category->parent_id=$parent; $category->status=1; $category->save();
    return $category;
}

test('admin dashboard uses live counts and admin guard only', function () {
    $this->actingAs(instructor())->get('/admin/dashboard')->assertRedirect();
    $this->actingAs(taxonomyAdmin(),'admin')->get('/admin/dashboard')->assertOk()->assertSee('أرقام المنصة الفعلية')->assertDontSee('Combo layout');
});

test('taxonomy create validates against correct table supports Arabic and stable slugs', function () {
    $this->actingAs(taxonomyAdmin(),'admin');
    CourseLanguage::create(['name'=>'مبتدئ','slug'=>'beginner-language']);
    $this->post('/admin/course-levels',['name'=>'مبتدئ'])->assertRedirect();
    $level=CourseLevel::sole(); $slug=$level->slug;
    $this->post('/admin/course-levels',['name'=>'مبتدئ'])->assertSessionHasErrors('name');
    $this->put('/admin/course-levels/'.$level->id,['name'=>'متقدم'])->assertRedirect();
    expect($level->fresh()->slug)->toBe($slug);
    $this->post('/admin/course-categories',['name'=>'البرمجة','status'=>1])->assertRedirect();
    $this->post('/admin/course-categories',['name'=>'التصميم','status'=>1])->assertRedirect();
    expect(CourseCategory::count())->toBe(2);
    expect(CourseCategory::pluck('slug')->unique())->toHaveCount(2);
});

test('nested taxonomy mutations are bound to parent and validate uploads', function () {
    $this->actingAs(taxonomyAdmin(),'admin');
    $a=categoryRecord('Parent A'); $b=categoryRecord('Parent B'); $child=categoryRecord('Child',$a->id);
    $this->get('/admin/course-categories/'.$b->id.'/sub-categories/'.$child->id.'/edit')->assertNotFound();
    $this->put('/admin/course-categories/'.$b->id.'/sub-categories/'.$child->id,['name'=>'Moved'])->assertNotFound();
    $this->delete('/admin/course-categories/'.$b->id.'/sub-categories/'.$child->id)->assertNotFound();
    $this->put('/admin/course-categories/'.$a->id.'/sub-categories/'.$child->id,['name'=>''])->assertSessionHasErrors('name');
    expect($child->fresh()->name)->toBe('Child');
});

test('in-use taxonomy and parent categories cannot be deleted', function () {
    $this->actingAs(taxonomyAdmin(),'admin');
    $parent=categoryRecord('Parent'); $child=categoryRecord('Child',$parent->id);
    $level=CourseLevel::create(['name'=>'Level','slug'=>'level']);
    $language=CourseLanguage::create(['name'=>'Language','slug'=>'language']);
    $course=publishedCourse(); $course->category_id=$child->id;$course->course_level_id=$level->id;$course->course_language_id=$language->id;$course->save();
    foreach(['/admin/course-categories/'.$parent->id, '/admin/course-categories/'.$parent->id.'/sub-categories/'.$child->id,'/admin/course-levels/'.$level->id,'/admin/course-languages/'.$language->id] as $url) $this->delete($url)->assertSessionHasErrors('delete');
    expect($level->fresh())->not->toBeNull();expect($child->fresh())->not->toBeNull();
});

test('instructor decisions require current request snapshot and private document before promotion', function () {
    Mail::fake();Storage::fake('local');Storage::disk('local')->put('instructor-documents/test.pdf','test');
    $applicant=User::factory()->create(['role'=>'student','approved_status'=>'pending','document'=>'instructor-documents/test.pdf']);
    $this->actingAs(taxonomyAdmin(),'admin');
    $url='/admin/instructors-requests/'.$applicant->id;
    $stale=InstructorReviewSnapshot::token($applicant);
    $applicant->name='Changed applicant';$applicant->save();
    $this->put($url,['status'=>'approved','snapshot'=>$stale])->assertConflict();
    $this->put($url,['status'=>'approved','snapshot'=>InstructorReviewSnapshot::token($applicant)])->assertRedirect();
    expect($applicant->fresh()->role)->toBe('instructor');
    $this->put($url,['status'=>'rejected','snapshot'=>InstructorReviewSnapshot::token($applicant->fresh())])->assertConflict();
    $missing=User::factory()->create(['role'=>'student','approved_status'=>'pending','document'=>'instructor-documents/missing.pdf']);
    $this->put('/admin/instructors-requests/'.$missing->id,['status'=>'approved','snapshot'=>InstructorReviewSnapshot::token($missing)])->assertUnprocessable();
    expect($missing->fresh()->role)->toBe('student');
});

test('taxonomy rejects executable uploads and deletes unused records without touching course data', function () {
    Storage::fake('public');
    $this->actingAs(taxonomyAdmin(),'admin');
    $this->post('/admin/course-categories',['name'=>'Bad image','status'=>1,'image'=>Illuminate\Http\UploadedFile::fake()->create('shell.php',10,'text/plain')])->assertSessionHasErrors('image');
    expect(CourseCategory::count())->toBe(0);
    $level=CourseLevel::create(['name'=>'Unused','slug'=>'unused']);
    $this->delete('/admin/course-levels/'.$level->id)->assertRedirect();
    expect($level->fresh())->toBeNull();
    $parent=categoryRecord('Parent');$child=categoryRecord('Child',$parent->id);
    $this->delete('/admin/course-categories/'.$parent->id.'/sub-categories/'.$child->id)->assertRedirect();
    expect($child->fresh())->toBeNull();expect($parent->fresh())->not->toBeNull();
});
