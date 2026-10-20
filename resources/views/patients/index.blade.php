@extends('layouts.app')
@section('title','إدارة المرضى')
@section('subtitle','سجل المرضى المرتبط بطلبات المخبر وأطباء الأسنان.')
@section('actions')
<a href="{{ route('patients.create') }}" class="btn btn-primary">＋ إضافة مريض</a>
@endsection

@section('content')<div class="panel"><div class="toolbar"><form method="GET"><input name="q" value="{{ request('q') }}" placeholder="البحث باسم المريض أو هاتفه" style="min-width:285px"><button class="btn btn-secondary">بحث</button>
<a class="btn btn-ghost" href="{{ route('patients.index') }}">مسح</a></form><span class="tag soft">{{ $patients->total() }} مريض</span></div><div class="table-wrap"><table><thead><tr><th>اسم المريض</th><th>الطبيب المتابع</th><th>الهاتف</th><th>تاريخ الميلاد</th><th>عدد الطلبات</th><th>إجراءات</th></tr></thead><tbody>@forelse($patients as $p)<tr><td><strong>{{ $p->name }}</strong></td><td>{{ $p->doctor?->name??'—' }}</td><td dir="ltr">{{ $p->phone??'—' }}</td><td>{{ $p->date_of_birth?->format('Y-m-d')??'—' }}</td><td>{{ $p->orders_count }}</td><td><div class="box-row"><a class="link-action" href="{{ route('patients.edit',$p) }}">تعديل</a><form method="POST" action="{{ route('patients.destroy',$p) }}" data-confirm="هل تريد حذف المريض؟">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">حذف</button></form></div></td></tr>@empty<tr><td colspan="6" class="empty">لا يوجد مرضى مسجلون بعد.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $patients->links() }}</div></div>@endsection