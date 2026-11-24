@extends('marketplace.layout')
@section('content')
<section class="hero"><div class="eyebrow">مساحتك للتعلّم</div><h1>خطوة جديدة، مهارة جديدة.</h1><p class="muted">استكشف الكورسات المنشورة، وابدأ التعلّم بالسرعة التي تناسبك.</p><form class="search" method="get"><input aria-label="ابحث عن كورس" name="q" value="{{ $term }}" placeholder="ماذا تريد أن تتعلّم؟"><button>ابحث</button></form></section>
<div class="row" style="margin-bottom:22px"><h2>الكورسات المتاحة</h2><span class="muted">{{ $courses->total() }} كورس منشور</span></div>
<div class="grid">@forelse($courses as $course)<article class="card"><span class="badge">{{ $course->price !== null && (float)$course->price === 0.0 ? 'مجاني' : 'مدفوع · قريبًا' }}</span><h2><a href="{{ route('marketplace.course', $course->slug) }}">{{ $course->title }}</a></h2><p class="muted">{{ \Illuminate\Support\Str::limit(strip_tags($course->description), 120) }}</p><a class="button secondary" href="{{ route('marketplace.course', $course->slug) }}">عرض التفاصيل</a></article>@empty<div class="empty" style="grid-column:1/-1">لا توجد كورسات منشورة تطابق البحث بعد.</div>@endforelse</div>
<div style="margin-top:24px">{{ $courses->links() }}</div>
@endsection
