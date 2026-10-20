<?php
namespace App\Http\Controllers;
use App\Models\{Doctor,Patient,LabOrder,Invoice,Payment};
class DashboardController extends Controller {
 public function index(){
  $orders=LabOrder::query();
  $counts=['doctors'=>Doctor::count(),'patients'=>Patient::count(),'orders'=>LabOrder::count(),
   'active'=>(clone $orders)->whereNotIn('status',['delivered','cancelled'])->count(),
   'late'=>LabOrder::whereDate('due_at','<',today())->whereNotIn('status',['delivered','cancelled'])->count(),
   'soon'=>LabOrder::whereBetween('due_at',[today(),today()->addDays(3)])->whereNotIn('status',['delivered','cancelled'])->count()];
  $revenue=(float)Payment::sum('amount');
  $outstanding=max(0,(float)Invoice::sum('total')-$revenue);
  $recent=LabOrder::with(['doctor','patient','workType'])->latest()->limit(8)->get();
  $urgent=LabOrder::with(['doctor','patient'])->whereDate('due_at','<=',today()->addDays(3))->whereNotIn('status',['delivered','cancelled'])->orderBy('due_at')->limit(8)->get();
  $statuses=LabOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status');
  return view('dashboard',compact('counts','revenue','outstanding','recent','urgent','statuses'));
 }
}
