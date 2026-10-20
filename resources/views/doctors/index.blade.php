@extends('layouts.app')
@section('title','الأطباء والعيادات')
@section('subtitle','إدارة معلومات أطباء الأسنان المتعاملين مع المخبر.')
@section('actions')
<a href="{{ route('doctors.create') }}" class="btn btn-primary">＋ إضافة طبيب</a>
@endsection

@section('content')<div class="panel"><div class="toolbar"><form method="GET"><input name="q" placeholder="بحث بالاسم أو رقم الهاتف أو العيادة" value="{{ request('q') }}" style="min-width:285px"><button class="btn btn-secondary">بحث</button>
<a class="btn btn-ghost" href="{{ route('doctors.index') }}">مسح</a></form><span class="tag soft">{{ $doctors->total() }} طبيب</span></div><div class="table-wrap"><table><thead><tr><th>اسم الطبيب</th><th>رقم الهاتف</th><th>العيادة</th><th>التخصص</th><th>الطلبات</th><th>إجراءات</th></tr></thead><tbody>@forelse($doctors as $d)<tr><td><strong>{{ $d->name }}</strong></td><td dir="ltr">{{ $d->phone??'—' }}</td><td>{{ $d->clinic_name??'—' }}</td><td>{{ $d->specialty??'—' }}</td><td>{{ $d->orders_count }}</td><td><div class="box-row"><a class="link-action" href="{{ route('doctors.edit',$d) }}">تعديل</a><form method="POST" action="{{ route('doctors.destroy',$d) }}" data-confirm="هل تريد حذف الطبيب؟">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">حذف</button></form></div></td></tr>@empty<tr><td class="empty" colspan="6">لا يوجد أطباء. أضف طبيبًا للبدء.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $doctors->links() }}</div></div>@endsection