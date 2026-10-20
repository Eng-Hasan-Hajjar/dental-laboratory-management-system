<?php
namespace App\Http\Controllers;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class DoctorController extends Controller {
 public function index(Request $r){$q=Doctor::withCount('orders')->latest();if($s=trim((string)$r->query('q'))){$q->where(fn($x)=>$x->where('name','like',"%{$s}%")->orWhere('phone','like',"%{$s}%")->orWhere('clinic_name','like',"%{$s}%"));}return view('doctors.index',['doctors'=>$q->paginate(12)->withQueryString()]);}
 public function create(){return view('doctors.form',['doctor'=>new Doctor]);}
 public function store(Request $r){Doctor::create($this->validated($r));return redirect()->route('doctors.index')->with('success','تمت إضافة الطبيب.');}
 public function edit(Doctor $doctor){return view('doctors.form',compact('doctor'));}
 public function update(Request $r,Doctor $doctor){$doctor->update($this->validated($r));return redirect()->route('doctors.index')->with('success','تم تحديث الطبيب.');}
 public function destroy(Doctor $doctor){if($doctor->orders()->exists())return back()->withErrors(['delete'=>'لا يمكن حذف طبيب مرتبط بطلبات.']);$doctor->delete();return back()->with('success','تم الحذف.');}
 private function validated(Request $r):array{return $r->validate(['name'=>['required','string','max:150'],'phone'=>['nullable','string','max:40'],'email'=>['nullable','email','max:255'],'specialty'=>['nullable','string','max:255'],'clinic_name'=>['nullable','string','max:255'],'address'=>['nullable','string','max:1000'],'notes'=>['nullable','string','max:2000']]);}
}
