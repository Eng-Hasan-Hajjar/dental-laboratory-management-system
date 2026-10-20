<?php
namespace App\Http\Controllers;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class WorkTypeController extends Controller {
 public function index(){return view('work_types.index',['types'=>WorkType::withCount('orders')->orderBy('name')->paginate(15)]);}
 public function create(){return view('work_types.form',['type'=>new WorkType]);}
 public function store(Request $r){WorkType::create($this->validated($r));return redirect()->route('work-types.index')->with('success','تمت إضافة نوع العمل.');}
 public function edit(WorkType $workType){return view('work_types.form',['type'=>$workType]);}
 public function update(Request $r,WorkType $workType){$workType->update($this->validated($r,$workType));return redirect()->route('work-types.index')->with('success','تم تحديث نوع العمل.');}
 public function destroy(WorkType $workType){if($workType->orders()->exists())return back()->withErrors(['delete'=>'نوع العمل مرتبط بطلبات.']);$workType->delete();return back()->with('success','تم الحذف.');}
 private function validated(Request $r,?WorkType $type=null):array{return $r->validate(['name'=>['required','string','max:150',Rule::unique('work_types','name')->ignore($type?->id)],'description'=>['nullable','string','max:1000'],'default_price'=>['required','numeric','decimal:0,2','min:0','max:999999999'],'estimated_days'=>['required','integer','min:1','max:365'],'is_active'=>['required','boolean']]);}
}
