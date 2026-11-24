@extends('marketplace.layout')
@section('content')
<section class="auth-grid"><div><div class="eyebrow">ThinkCore · 419</div><h1 style="font-size:36px">انتهت صلاحية الجلسة.</h1><p class="muted" style="margin-top:20px">ارجع للصفحة وسجّل دخولك من جديد قبل إعادة المحاولة.</p><a class="button" href="{{ route('marketplace.catalog') }}">العودة للاستكشاف ←</a></div><div class="auth-graphic"><strong style="color:var(--brand)">419</strong><p>خطوة بسيطة، ونرجع نكمّل.</p></div></section>
@endsection
