<?php
namespace App\Http\Controllers;
use App\Models\{LabOrder,Doctor,Patient,WorkType,User,Invoice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class LabOrderController extends Controller {
 public function index(Request $r){
  $q=LabOrder::with(['doctor','patient','workType','technician'])->latest();
  if($s=trim((string)$r->query('q'))){$q->where(fn($x)=>$x->where('order_no','like',"%{$s}%")->orWhereHas('patient',fn($p)=>$p->where('name','like',"%{$s}%"))->orWhereHas('doctor',fn($d)=>$d->where('name','like',"%{$s}%")));}
  if($r->filled('status') && array_key_exists($r->status,LabOrder::STATUSES))$q->where('status',$r->status);
  if($r->boolean('late'))$q->whereDate('due_at','<',today())->whereNotIn('status',['delivered','cancelled']);
  return view('orders.index',['orders'=>$q->paginate(12)->withQueryString()]);
 }
 public function create(){return $this->form(new LabOrder);}
 public function edit(LabOrder $order){return $this->form($order);}
 private function form(LabOrder $order){return view('orders.form',['order'=>$order,'doctors'=>Doctor::orderBy('name')->get(),'patients'=>Patient::orderBy('name')->get(),'types'=>WorkType::where('is_active',true)->orWhere('id',$order->work_type_id)->get(),'staff'=>User::where('is_active',true)->whereIn('role',['admin','manager','technician'])->orderBy('name')->get()]);}
 public function store(Request $r){$data=$this->validated($r);$data['order_no']='LAB-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(4)));$data['status']='received';
  $order=DB::transaction(function()use($r,$data){
   $order=LabOrder::create($data);
   $order->history()->create(['user_id'=>$r->user()->id,'to_status'=>'received','note'=>'إنشاء الطلب']);
   Invoice::create(['number'=>'INV-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(4))),'lab_order_id'=>$order->id,'subtotal'=>$order->price,'discount'=>0,'tax'=>0,'total'=>$order->price,'issued_at'=>today()]);
   return $order;
  });
  return redirect()->route('orders.show',$order)->with('success','تم إنشاء الطلب والفاتورة المرتبطة بنجاح.');
 }
 public function update(Request $r,LabOrder $order){$data=$this->validated($r);$order->update($data);return redirect()->route('orders.show',$order)->with('success','تم تحديث الطلب. الأسعار في الفاتورة مستقلة عن سعر الطلب.');}
 public function show(LabOrder $order){$order->load(['doctor','patient','workType','technician','history.user','invoice.payments.user']);return view('orders.show',compact('order'));}
 public function status(Request $r,LabOrder $order){
  $data=$r->validate(['status'=>['required','string'],'note'=>['nullable','string','max:1000']]);
  DB::transaction(function()use($order,$r,$data){
   $locked=LabOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
   if($r->user()->role==='technician' && $locked->assigned_to!==$r->user()->id)abort(403,'هذا الطلب غير مكلّف لك.');
   if(!in_array($data['status'],$locked->nextStatuses(),true)){throw ValidationException::withMessages(['status'=>'انتقال الحالة غير مسموح. اتبع مراحل سير العمل.']);}
   $old=$locked->status;$locked->update(['status'=>$data['status']]);$locked->history()->create(['user_id'=>$r->user()->id,'from_status'=>$old,'to_status'=>$data['status'],'note'=>$data['note']??null]);
  });return back()->with('success','تم تحديث مرحلة التنفيذ.');
 }
 public function destroy(LabOrder $order){
  if($order->invoice?->payments()->exists())return back()->withErrors(['delete'=>'لا يمكن حذف طلب له مدفوعات مسجلة.']);
  DB::transaction(function()use($order){$order->invoice?->delete();$order->delete();});
  return redirect()->route('orders.index')->with('success','تم حذف الطلب والفاتورة غير المدفوعة.');
 }
 private function validated(Request $r):array{
  $data=$r->validate(['doctor_id'=>['required','exists:doctors,id'],'patient_id'=>['required','exists:patients,id'],'work_type_id'=>['required','exists:work_types,id'],'assigned_to'=>['nullable','exists:users,id'],'shade'=>['nullable','string','max:50'],'material'=>['nullable','string','max:255'],'tooth_numbers'=>['nullable','string','max:255'],'instructions'=>['nullable','string','max:5000'],'received_at'=>['required','date'],'due_at'=>['required','date','after_or_equal:received_at'],'price'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'priority'=>['required','in:normal,high,urgent']]);
  $patient=Patient::find($data['patient_id']);
  if($patient->doctor_id && (int)$patient->doctor_id !== (int)$data['doctor_id'])throw ValidationException::withMessages(['patient_id'=>'المريض مسجل لدى طبيب آخر. عدّل الطبيب أو بيانات المريض أولاً.']);
  if(!empty($data['assigned_to']) && !User::whereKey($data['assigned_to'])->where('is_active',true)->whereIn('role',['admin','manager','technician'])->exists())throw ValidationException::withMessages(['assigned_to'=>'الموظف المحدد غير متاح للتكليف.']);
  return $data;
 }
}
