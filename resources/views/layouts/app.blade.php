<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
 <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title','لوحة التحكم') | مختبر الأسنان</title>
 <link rel="stylesheet" href="{{ asset('css/app.css') }}">
 <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
 <div class="app-shell">
  <aside class="sidebar" id="sidebar">
   <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">✦</span><span><strong>Dental Lab</strong><small>نظام إدارة مخابر الأسنان</small></span></a>
   <div class="sidebar-caption">القائمة الرئيسية</div>
   <nav class="nav">
    <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><span>▦</span> نظرة عامة</a>
    <a class="nav-link {{ request()->routeIs('orders.*')?'active':'' }}" href="{{ route('orders.index') }}"><span>▤</span> الطلبات والحالات</a>
    @if(in_array(auth()->user()->role,['admin','manager','receptionist']))
    <a class="nav-link {{ request()->routeIs('doctors.*')?'active':'' }}" href="{{ route('doctors.index') }}"><span>♧</span> الأطباء والعيادات</a>
    <a class="nav-link {{ request()->routeIs('patients.*')?'active':'' }}" href="{{ route('patients.index') }}"><span>♙</span> المرضى</a>
    @endif
    @if(auth()->user()->canManage())<a class="nav-link {{ request()->routeIs('work-types.*')?'active':'' }}" href="{{ route('work-types.index') }}"><span>◈</span> أنواع الأعمال</a>@endif
    @if(auth()->user()->isFinance())
    <div class="sidebar-caption">المالية والتقارير</div>
    <a class="nav-link {{ request()->routeIs('invoices.*')?'active':'' }}" href="{{ route('invoices.index') }}"><span>▣</span> الفواتير والمدفوعات</a>
    <a class="nav-link {{ request()->routeIs('reports.*')?'active':'' }}" href="{{ route('reports.index') }}"><span>▥</span> التقارير والإحصائيات</a>
    @endif
    @if(auth()->user()->role==='admin')<div class="sidebar-caption">الإدارة</div><a class="nav-link {{ request()->routeIs('users.*')?'active':'' }}" href="{{ route('users.index') }}"><span>☷</span> حسابات الموظفين</a>@endif
   </nav>
   <div class="sidebar-bottom"><span class="pulse"></span> جامعة حلب <small>مشروع تخرج • Laravel 12</small></div>
  </aside>
  <div class="main-area">
   <header class="topbar"><button type="button" class="menu-toggle" id="menuToggle" aria-label="فتح القائمة">☰</button><div class="top-title">@yield('top-title','إدارة المخبر')</div><div class="top-user"><span class="avatar">{{ mb_substr(auth()->user()->name,0,1) }}</span><span class="user-meta"><b>{{ auth()->user()->name }}</b><small>{{ ['admin'=>'مدير النظام','manager'=>'مدير المخبر','technician'=>'فني','accountant'=>'محاسب','receptionist'=>'موظف استقبال'][auth()->user()->role]??auth()->user()->role }}</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-xs" type="submit">خروج</button></form></div></header>
   <main class="content">
    <div class="page-head"><div><div class="eyebrow">DENTAL LAB / جامعة حلب</div><h1>@yield('title','لوحة التحكم')</h1><p>@yield('subtitle','مساحة عمل موحدة لإدارة عمليات المخبر بدقة ووضوح.')</p></div><div class="head-actions">@yield('actions')</div></div>
    @if(session('success'))<div class="alert success">✓ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert danger"><b>يرجى تصحيح الأخطاء التالية:</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
    <footer class="footer">© {{ date('Y') }} Dental Laboratory Management System — جامعة حلب</footer>
   </main>
  </div>
 </div>
</body></html>
