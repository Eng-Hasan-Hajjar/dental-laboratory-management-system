<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller {
 public function index(){return view('users.index',['users'=>User::orderBy('name')->paginate(20)]);}
 public function create(){return view('users.form',['staffUser'=>new User]);}
 public function edit(User $user){return view('users.form',['staffUser'=>$user]);}
 public function store(Request $r){$data=$this->validated($r);$data['password']=Hash::make($data['password']);User::create($data);return redirect()->route('users.index')->with('success','تم إنشاء الحساب.');}
 public function update(Request $r,User $user){$data=$this->validated($r,$user);if(empty($data['password']))unset($data['password']);else $data['password']=Hash::make($data['password']);if($r->user()->is($user) && ($data['role']!=='admin'||!(bool)$data['is_active']))return back()->withErrors(['role'=>'لا يمكنك سحب صلاحيات الإدارة أو تعطيل حسابك الحالي.']);$user->update($data);return redirect()->route('users.index')->with('success','تم تحديث الحساب.');}
 private function validated(Request $r,?User $user=null):array{return $r->validate(['name'=>['required','string','max:150'],'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($user?->id)],'role'=>['required','in:admin,manager,technician,accountant,receptionist'],'is_active'=>['required','boolean'],'password'=>[$user?'nullable':'required','string',Password::min(12)]]);}
}
