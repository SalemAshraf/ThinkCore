@extends('marketplace.layout')
@section('content')
<section class="auth-grid"><div><div class="eyebrow">ThinkCore · 403</div><h1 style="font-size:36px">هذه المساحة غير متاحة لك.</h1><p class="muted" style="margin-top:20px">قد تحتاج للاشتراك في الكورس أو الدخول بحساب لديه الصلاحية المطلوبة.</p><a class="button" href="{{ route('marketplace.catalog') }}">العودة للاستكشاف ←</a></div><div class="auth-graphic"><strong style="color:var(--brand)">403</strong><p>خطوة بسيطة، ونرجع نكمّل.</p></div></section>
@endsection
