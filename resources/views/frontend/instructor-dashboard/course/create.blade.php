@extends('frontend.instructor-dashboard.course.course-app')
@section('course-content')
<h2>البيانات الأساسية</h2><p class="muted">عرّف المتعلّمين بما سيجدونه في كورسك.</p>
<form method="post" enctype="multipart/form-data" action="{{ isset($course) ? route('instructor.courses.moreinfo') : route('instructor.courses.store.basic_info') }}">@csrf
@if(isset($course))<input type="hidden" name="id" value="{{ $course->id }}">@endif<input type="hidden" name="current_step" value="1">
<label for="title">عنوان الكورس</label><input id="title" name="title" value="{{ old('title', $course->title ?? '') }}" required maxlength="255" placeholder="مثال: أساسيات تطوير الويب من الصفر">
<label for="description">عن الكورس</label><textarea id="description" name="description" rows="7" required>{{ old('description', $course->description ?? '') }}</textarea>
<label for="seo_description">وصف قصير لمحركات البحث</label><input id="seo_description" name="seo_description" maxlength="255" value="{{ old('seo_description', $course->seo_description ?? '') }}">
<label for="thumbnail">صورة غلاف الكورس</label><input id="thumbnail" name="thumbnail" type="file" accept=".jpg,.jpeg,.png,.gif"><small>صورة بحد أقصى 2 ميجابايت. ترك الحقل فارغًا يحافظ على الصورة الحالية.</small>
<div class="form-grid"><div><label for="price">السعر المسجّل</label><input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $course->price ?? 0) }}" required><small>اكتب 0 لكورس مجاني. الانضمام المدفوع غير متاح.</small></div><div><label for="discount">قيمة الخصم المسجّلة</label><input id="discount" name="discount" type="number" step="0.01" min="0" value="{{ old('discount', $course->discount ?? 0) }}"></div></div>
<details class="advanced"><summary>بيانات فيديو تعريفي اختيارية (لا يتم تشغيله حاليًا)</summary><label for="demo_video">مصدر الفيديو</label><select id="demo_video" name="demo_video"><option value="">بدون فيديو</option>@foreach(['youtube'=>'YouTube','vimeo'=>'Vimeo','link'=>'رابط خارجي'] as $value=>$label)<option value="{{ $value }}" @selected(old('demo_video', $course->demo_video ?? '')===$value)>{{ $label }}</option>@endforeach</select><label for="demo_video_source">رابط الفيديو</label><input id="demo_video_source" name="demo_video_source" type="url" dir="ltr" value="{{ old('demo_video_source', $course->demo_video_source ?? '') }}"></details>
<div class="form-actions"><button type="submit">حفظ ومتابعة للإعدادات ←</button><a href="{{ route('instructor.courses') }}">العودة للكورسات</a></div></form>
@endsection
