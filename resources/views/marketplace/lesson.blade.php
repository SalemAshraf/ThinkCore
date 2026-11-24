@extends('marketplace.layout')
@section('content')
<a href="{{ route('marketplace.course', $course->slug) }}">{{ $course->title }}</a><h1>{{ $lesson->title }}</h1><article class="panel lesson">{{ $lesson->content }}</article>
@if($completed)<p class="notice" style="margin-top:24px">أكملت هذا الدرس.</p>@endif
@if($enrolled && !$completed)<form style="margin-top:24px" method="post" action="{{ route('marketplace.complete', [$course->slug, $lesson->id]) }}">@csrf<button>أنهيت هذا الدرس</button></form>@endif
<nav class="row" style="padding:24px 0">@if($previous)<a class="button secondary" href="{{ route('marketplace.lesson', [$course->slug, $previous->id]) }}">السابق: {{ $previous->title }}</a>@endif @if($next)<a class="button" href="{{ route('marketplace.lesson', [$course->slug, $next->id]) }}">التالي: {{ $next->title }}</a>@else<a href="{{ route('marketplace.course', $course->slug) }}">العودة لمحتوى الكورس</a>@endif</nav>
@endsection
