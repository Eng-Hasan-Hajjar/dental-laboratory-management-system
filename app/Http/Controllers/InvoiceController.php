<?php
namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InvoiceController extends Controller {
 public function index(Request $r){$q=Invoice::with(['order.patient','order.doctor'])->withSum('payments','amount')->latest();if($s=trim((string)$r->query('q'))){$q->where(fn($x)=>$x->where('number','like',"%{$s}%")->orWhereHas('order',fn($o)=>$o->where('order_no','like',"%{$s}%")));}return view('invoices.index',['invoices'=>$q->paginate(12)->withQueryString()]);}
 public function show(Invoice $invoice){$invoice->load(['order.doctor','order.patient','order.workType','payments.user']);return view('invoices.show',compact('invoice'));}
 public function edit(Invoice $invoice){$invoice->load('order');return view('invoices.form',compact('invoice'));}
 public function update(Request $r,Invoice $invoice){
  $data=$r->validate(['subtotal'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'discount'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'tax'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'notes'=>['nullable','string','max:2000']]);
  DB::transaction(function()use($invoice,$data){
   $locked=Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
   $total=round((float)$data['subtotal']-(float)$data['discount']+(float)$data['tax'],2);
   if($total<0)throw ValidationException::withMessages(['discount'=>'لا يمكن أن يتجاوز الخصم مجموع السعر والضريبة.']);
   $paid=(float)$locked->payments()->sum('amount');
   if(round($total*100)<round($paid*100))throw ValidationException::withMessages(['subtotal'=>'لا يمكن أن يصبح إجمالي الفاتورة أقل من المدفوع.']);
   $locked->update([...$data,'total'=>$total]);
  });return redirect()->route('invoices.show',$invoice)->with('success','تم تعديل الفاتورة.');
 }
}
