@extends('layouts.app')
@section('title','إدارة حسابات الموظفين')
@section('subtitle','مدير النظام يتحكم بإنشاء الحسابات وصلاحياتها وتعطيلها.')
@section('actions')
<a href="{{ route('users.create') }}" class="btn btn-primary">＋ حساب جديد</a>
@endsection

@section('content')<div class="panel"><div class="table-wrap"><table><thead><tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>الحالة</th><th>تاريخ الإنشاء</th><th></th></tr></thead><tbody>@foreach($users as $u)<tr><td>{{ $u->name }}</td><td dir="ltr">{{ $u->email }}</td><td>{{ ['admin'=>'مدير نظام','manager'=>'مدير مخبر','technician'=>'فني','accountant'=>'محاسب','receptionist'=>'استقبال'][$u->role]??$u->role }}</td><td><span class="tag {{ $u->is_active?'soft':'red' }}">{{ $u->is_active?'مفعّل':'معطّل' }}</span></td><td>{{ $u->created_at->format('Y-m-d') }}</td><td><a class="link-action" href="{{ route('users.edit',$u) }}">تعديل</a></td></tr>@endforeach</tbody></table></div><div class="pagination">{{ $users->links() }}</div></div>@endsection