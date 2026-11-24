<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title', 'ThinkCore | مهارتك الجاية تبدأ هنا')</title><link rel="stylesheet" href="{{ asset('marketplace/thinkcore.css') }}"></head>
<body>
<div class="topnote">مساحة لمهارتك الجاية. <span>اكتشف الكورسات المجانية وابدأ التعلّم ←</span></div>
<header><nav class="nav" aria-label="التنقل الرئيسي"><a class="logo" href="{{ route('home') }}"><b>t</b><div>think<span>core</span></div></a><a class="catalog-link" href="{{ route('marketplace.catalog') }}">استكشف الكورسات</a><form class="navsearch" method="get" action="{{ route('marketplace.catalog') }}"><input name="q" aria-label="ابحث عن كورس" placeholder="عن أي مهارة تبحث؟"><button aria-label="بحث">⌕</button></form><div class="navlinks">@auth<a href="{{ route('marketplace.library') }}">تعلّمي</a><a class="button secondary" href="{{ route('dashboard') }}">حسابي</a>@else<a href="{{ route('login') }}">دخول</a><a class="button" href="{{ route('register') }}">ابدأ رحلتك</a>@endauth</div></nav></header>
<main>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="error" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@yield('content')
</main>
@auth<div class="container account-links"><a href="{{ route('profile.edit') }}">إعدادات الحساب</a><form id="logout-form" method="post" action="{{ route('logout') }}">@csrf<button class="text-button" type="submit">تسجيل الخروج</button></form></div>@endauth
<footer><div class="footer-main"><div><a class="logo" href="{{ route('home') }}"><b>t</b><div>think<span>core</span></div></a><p>كل مهارة جديدة بتفتح لك باب. استكشف، اتعلّم، وكمّل رحلتك خطوة بخطوة.</p></div><div class="footer-links"><a href="{{ route('marketplace.catalog') }}">الكورسات</a>@auth<a href="{{ route('marketplace.library') }}">تعلّمي</a>@else<a href="{{ route('register') }}">انضم إلينا</a>@endauth</div></div><div class="footer-bottom"><span>ThinkCore · مساحة للتعلّم المستمر</span><span>نسخة تطوير · المتاح حاليًا: كورسات نصية مجانية. الدفع والمحتوى المدفوع غير متاحين بعد.</span></div></footer>
</body></html>
