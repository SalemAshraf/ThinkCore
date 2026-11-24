@extends('marketplace.layout')
@section('content')
<a href="{{ route('marketplace.course', $course->slug) }}">{{ $course->title }}</a><h1>{{ $lesson->title }}</h1><article class="panel lesson">{{ $lesson->content }}</article>
@if($enrolled)<form style="margin-top:24px" method="post" action="{{ route('marketplace.complete', [$course->slug, $lesson->id]) }}">@csrf<button>أنهيت هذا الدرس</button></form>@endif
@endsection
