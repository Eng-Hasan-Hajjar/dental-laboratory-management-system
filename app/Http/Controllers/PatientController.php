<?php
namespace App\Http\Controllers;
use App\Models\{Patient,Doctor};
use Illuminate\Http\Request;
class PatientController extends Controller {
 public function index(Request $r){$q=Patient::with('doctor')->withCount('orders')->latest();if($s=trim((string)$r->query('q'))){$q->where(fn($x)=>$x->where('name','like',"%{$s}%")->orWhere('phone','like',"%{$s}%"));}return view('patients.index',['patients'=>$q->paginate(12)->withQueryString()]);}
 public function create(){return view('patients.form',['patient'=>new Patient,'doctors'=>Doctor::orderBy('name')->get()]);}
 public function store(Request $r){Patient::create($this->validated($r));return redirect()->route('patients.index')->with('success','تم تسجيل المريض.');}
 public function edit(Patient $patient){return view('patients.form',['patient'=>$patient,'doctors'=>Doctor::orderBy('name')->get()]);}
 public function update(Request $r,Patient $patient){$patient->update($this->validated($r));return redirect()->route('patients.index')->with('success','تم تحديث بيانات المريض.');}
 public function destroy(Patient $patient){if($patient->orders()->exists())return back()->withErrors(['delete'=>'لا يمكن حذف مريض مرتبط بطلبات.']);$patient->delete();return back()->with('success','تم الحذف.');}
 private function validated(Request $r):array{return $r->validate(['name'=>['required','string','max:150'],'phone'=>['nullable','string','max:40'],'date_of_birth'=>['nullable','date','before_or_equal:today'],'gender'=>['nullable','in:male,female'],'doctor_id'=>['nullable','exists:doctors,id'],'notes'=>['nullable','string','max:2000']]);}
}
