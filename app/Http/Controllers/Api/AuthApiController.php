<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthApiController extends Controller {
 public function login(Request $r){$d=$r->validate(['email'=>['required','email'],'password'=>['required','string'],'device_name'=>['required','string','max:100']]);$user=User::where('email',$d['email'])->where('is_active',true)->first();if(!$user||!Hash::check($d['password'],$user->password))throw ValidationException::withMessages(['email'=>'بيانات الدخول غير صحيحة.']);return response()->json(['token'=>$user->createToken($d['device_name'],['*'],now()->addDays(30))->plainTextToken,'token_type'=>'Bearer','user'=>$user]);}
 public function me(Request $r){return response()->json($r->user());}
 public function logout(Request $r){$r->user()->currentAccessToken()?->delete();return response()->json(['message'=>'تم إبطال الرمز.']);}
}
