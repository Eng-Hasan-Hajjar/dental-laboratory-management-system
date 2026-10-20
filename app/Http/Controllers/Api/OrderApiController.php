<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{LabOrder,Patient,User,Invoice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class OrderApiController extends Controller {
 public function index(Request $r){$q=LabOrder::with(['doctor:id,name','patient:id,name','workType:id,name']);if($r->filled('status')){$r->validate(['status'=>'in:'.implode(',',array_keys(LabOrder::STATUSES))]);$q->where('status',$r->status);}return response()->json($q->latest()->paginate(min(50,max(1,(int)$r->query('per_page',15)))));}
 public function show(LabOrder $order){return response()->json($order->load(['doctor','patient','workType','history.user:id,name','invoice.payments']));}
 public function store(Request $r){
  $d=$r->validate(['doctor_id'=>['required','exists:doctors,id'],'patient_id'=>['required','exists:patients,id'],'work_type_id'=>['required','exists:work_types,id'],'assigned_to'=>['nullable','exists:users,id'],'shade'=>['nullable','string','max:50'],'material'=>['nullable','string','max:255'],'tooth_numbers'=>['nullable','string','max:255'],'instructions'=>['nullable','string','max:5000'],'received_at'=>['required','date'],'due_at'=>['required','date','after_or_equal:received_at'],'price'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'priority'=>['required','in:normal,high,urgent']]);
  $patient=Patient::find($d['patient_id']);if($patient->doctor_id && (int)$patient->doctor_id !== (int)$d['doctor_id'])throw ValidationException::withMessages(['patient_id'=>'المريض مرتبط بطبيب آخر.']);
  if(!empty($d['assigned_to']) && !User::whereKey($d['assigned_to'])->where('is_active',true)->whereIn('role',['admin','manager','technician'])->exists())throw ValidationException::withMessages(['assigned_to'=>'الموظف غير متاح.']);
  $order=DB::transaction(function()use($r,$d){$order=LabOrder::create([...$d,'order_no'=>'LAB-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(4))),'status'=>'received']);$order->history()->create(['user_id'=>$r->user()->id,'to_status'=>'received','note'=>'إنشاء عبر API']);Invoice::create(['number'=>'INV-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(4))),'lab_order_id'=>$order->id,'subtotal'=>$order->price,'discount'=>0,'tax'=>0,'total'=>$order->price,'issued_at'=>today()]);return $order;});
  return response()->json($order->load('invoice'),201);
 }
 public function status(Request $r,LabOrder $order){$d=$r->validate(['status'=>['required','string'],'note'=>['nullable','string','max:1000']]);DB::transaction(function()use($r,$d,$order){$current=LabOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();if($r->user()->role==='technician' && $current->assigned_to!==$r->user()->id)abort(403,'هذا الطلب غير مكلّف لك.');if(!in_array($d['status'],$current->nextStatuses(),true))throw ValidationException::withMessages(['status'=>'الانتقال غير مسموح.']);$before=$current->status;$current->update(['status'=>$d['status']]);$current->history()->create(['user_id'=>$r->user()->id,'from_status'=>$before,'to_status'=>$d['status'],'note'=>$d['note']??null]);});return response()->json($order->fresh()->load('history'));}
}
