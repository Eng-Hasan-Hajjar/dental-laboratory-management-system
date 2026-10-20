<?php
namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class PaymentController extends Controller {
 public function store(Request $r,Invoice $invoice){
  $data=$r->validate(['amount'=>['required','numeric','gt:0','decimal:0,2','max:999999999'],'method'=>['required','in:cash,bank,card,other'],'reference'=>['nullable','string','max:255'],'paid_at'=>['required','date','before_or_equal:today'],'notes'=>['nullable','string','max:2000']]);
  DB::transaction(function()use($r,$invoice,$data){
   $locked=Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
   $paid=(float)$locked->payments()->sum('amount');
   if((int)round(((float)$data['amount']+$paid)*100)>(int)round((float)$locked->total*100))throw ValidationException::withMessages(['amount'=>'الدفعة أكبر من الرصيد المتبقي.']);
   $locked->payments()->create([...$data,'user_id'=>$r->user()->id]);
  });return redirect()->route('invoices.show',$invoice)->with('success','تم تسجيل الدفعة بنجاح.');
 }
}
