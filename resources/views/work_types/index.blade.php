@extends('layouts.app')
@section('title','أنواع الأعمال المخبرية')
@section('subtitle','كتالوج الخدمات والأسعار الاسترشادية والمدة المقدرة لكل عمل.')
@section('actions')
<a class="btn btn-primary" href="{{ route('work-types.create') }}">＋ نوع عمل جديد</a>
@endsection

@section('content')<div class="panel"><div class="table-wrap"><table><thead><tr><th>اسم العمل</th><th>السعر الافتراضي</th><th>مدة التنفيذ</th><th>الحالة</th><th>عدد الطلبات</th><th>إجراءات</th></tr></thead><tbody>@forelse($types as $t)<tr><td><strong>{{ $t->name }}</strong><small style="display:block;color:#9aa7ad">{{ $t->description }}</small></td><td>{{ number_format((float)$t->default_price,2) }} USD</td><td>{{ $t->estimated_days }} يوم</td><td><span class="tag {{ $t->is_active?'soft':'red' }}">{{ $t->is_active?'نشط':'معطّل' }}</span></td><td>{{ $t->orders_count }}</td><td><div class="box-row"><a class="link-action" href="{{ route('work-types.edit',$t) }}">تعديل</a><form method="POST" action="{{ route('work-types.destroy',$t) }}" data-confirm="تأكيد حذف هذا النوع؟">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">حذف</button></form></div></td></tr>@empty<tr><td colspan="6" class="empty">لا توجد أنواع أعمال.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $types->links() }}</div></div>@endsection