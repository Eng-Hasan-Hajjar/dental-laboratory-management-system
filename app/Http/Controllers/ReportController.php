<?php
namespace App\Http\Controllers;
use App\Models\{LabOrder,Invoice,Payment,WorkType};
use Illuminate\Http\Request;
class ReportController extends Controller {
 public function index(Request $r){
  $data=$r->validate(['from'=>['nullable','date'],'to'=>['nullable','date','after_or_equal:from']]);
  $from=$data['from']??now()->startOfMonth()->toDateString();$to=$data['to']??today()->toDateString();
  $orders=LabOrder::whereBetween('received_at',[$from,$to]);
  $payments=Payment::whereBetween('paid_at',[$from,$to]);
  $invoices=Invoice::whereBetween('issued_at',[$from,$to]);
  $metrics=['orders'=>(clone $orders)->count(),'invoiced'=>(float)$invoices->sum('total'),'collected'=>(float)$payments->sum('amount')];
  $byStatus=(clone $orders)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status');
  $byType=(clone $orders)->selectRaw('work_type_id, count(*) as total')->groupBy('work_type_id')->pluck('total','work_type_id');
  $types=WorkType::whereIn('id',$byType->keys())->pluck('name','id');
  $rows=(clone $orders)->with(['doctor','patient','workType'])->latest()->paginate(20)->withQueryString();
  return view('reports.index',compact('from','to','metrics','byStatus','byType','types','rows'));
 }
 public function csv(Request $r){
  $data=$r->validate(['from'=>['required','date'],'to'=>['required','date','after_or_equal:from']]);
  $rows=LabOrder::with(['doctor','patient','workType'])->whereBetween('received_at',[$data['from'],$data['to']])->orderBy('id')->get();
  return response()->streamDownload(function()use($rows){$f=fopen('php://output','w');fwrite($f,"\xEF\xBB\xBF");fputcsv($f,['رقم الطلب','الطبيب','المريض','نوع العمل','الحالة','تاريخ الاستلام','موعد التسليم','التكلفة']);foreach($rows as $o){$clean=fn($v)=>preg_match('/^[=+@\-\t\r]/u',(string)$v)?"'".$v:$v;fputcsv($f,array_map($clean,[$o->order_no,$o->doctor?->name,$o->patient?->name,$o->workType?->name,$o->status_label,$o->received_at?->toDateString(),$o->due_at?->toDateString(),$o->price]));}fclose($f);},'dental-orders.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
 }
}
