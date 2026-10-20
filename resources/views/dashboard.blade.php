@extends('layouts.app')
@section('title','لوحة التحكم')
@section('subtitle','متابعة أداء المخبر وحالات التسليم في الوقت الحقيقي عند تحديث الصفحة.')
@section('actions')
@if(in_array(auth()->user()->role,['admin','manager','receptionist']))
<a class="btn btn-primary" href="{{ route('orders.create') }}">＋ طلب مخبري جديد</a>
@endif
@endsection

@section('content')
<div class="stats-grid">
 <div class="stat-card"><div class="stat-icon cyan">▤</div><div class="stat-name">إجمالي الطلبات</div><div class="stat-value">{{ number_format($counts['orders']) }}</div><div class="stat-info">جميع الحالات المسجلة</div></div>
 <div class="stat-card"><div class="stat-icon blue">◈</div><div class="stat-name">قيد العمل</div><div class="stat-value">{{ number_format($counts['active']) }}</div><div class="stat-info">طلب لم يُغلق بعد</div></div>
 <div class="stat-card"><div class="stat-icon amber">◷</div><div class="stat-name">قريبة من التسليم</div><div class="stat-value">{{ number_format($counts['soon']) }}</div><div class="stat-info">خلال الأيام الثلاثة القادمة</div></div>
 <div class="stat-card"><div class="stat-icon red">!</div><div class="stat-name">طلبات متأخرة</div><div class="stat-value">{{ number_format($counts['late']) }}</div><div class="stat-info">تتطلب متابعة عاجلة</div></div>
</div>
@if(auth()->user()->isFinance())<div class="finance-strip"><div><small>إجمالي المبالغ المحصّلة</small><strong>{{ number_format($revenue,2) }} <span>USD</span></strong></div><div class="finance-line"></div><div><small>إجمالي الرصيد المتبقي</small><strong>{{ number_format($outstanding,2) }} <span>USD</span></strong></div><a href="{{ route('reports.index') }}" class="btn btn-outline-light">عرض التقارير ←</a></div>@endif
<div class="two-columns">
 <section class="panel"><div class="panel-title"><div><h2>توزيع الحالات</h2><p>حالة الطلبات ضمن مراحل العمل</p></div></div><div class="status-bars">@foreach(\App\Models\LabOrder::STATUSES as $key=>$name) @php($num=(int)($statuses[$key]??0))<div class="bar-row"><span>{{ $name }}</span><div class="bar-track"><div class="bar-fill" style="width:{{ $counts['orders']>0?round($num/$counts['orders']*100):0 }}%"></div></div><b>{{ $num }}</b></div>@endforeach</div></section>
 <section class="panel"><div class="panel-title"><div><h2>متابعة المواعيد</h2><p>المهام المتأخرة والقريبة من التسليم</p></div><span class="tag orange">تنبيه</span></div>@forelse($urgent as $order)<a class="reminder" href="{{ route('orders.show',$order) }}"><span class="reminder-icon">◷</span><span class="reminder-body"><strong>{{ $order->order_no }}</strong><small>{{ $order->patient->name }} • {{ $order->doctor->name }}</small></span><span class="{{ $order->is_late?'tag red':'tag amber' }}">{{ $order->due_at->format('Y-m-d') }}</span></a>@empty<div class="empty">لا توجد طلبات متأخرة أو قريبة من موعد التسليم.</div>@endforelse</section>
</div>
<div class="panel"><div class="panel-title"><div><h2>آخر الطلبات</h2><p>أحدث الحالات التي أُضيفت إلى المخبر</p></div><a href="{{ route('orders.index') }}" class="text-link">عرض جميع الطلبات ←</a></div><div class="table-wrap"><table><thead><tr><th>رقم الطلب</th><th>الطبيب</th><th>المريض</th><th>العمل</th><th>التسليم</th><th>الحالة</th><th></th></tr></thead><tbody>@forelse($recent as $o)<tr><td class="mono"><a href="{{ route('orders.show',$o) }}">{{ $o->order_no }}</a></td><td>{{ $o->doctor->name }}</td><td>{{ $o->patient->name }}</td><td>{{ $o->workType->name }}</td><td>{{ $o->due_at->format('Y-m-d') }}</td><td><span class="tag {{ $o->is_late?'red':'soft' }}">{{ $o->status_label }}</span></td><td><a class="link-action" href="{{ route('orders.show',$o) }}">تفاصيل ←</a></td></tr>@empty<tr><td colspan="7" class="empty">لم يتم تسجيل طلبات بعد. يمكنك إضافة الطلب الأول.</td></tr>@endforelse</tbody></table></div></div>
@endsection