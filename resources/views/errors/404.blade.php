@extends('marketplace.layout')
@section('content')
<section class="auth-grid"><div><div class="eyebrow">ThinkCore · 404</div><h1 style="font-size:36px">الصفحة مش موجودة.</h1><p class="muted" style="margin-top:20px">قد يكون الرابط غير صحيح، أو المحتوى غير منشور حاليًا.</p><a class="button" href="{{ route('marketplace.catalog') }}">العودة للاستكشاف ←</a></div><div class="auth-graphic"><strong style="color:var(--brand)">404</strong><p>خطوة بسيطة، ونرجع نكمّل.</p></div></section>
@endsection
