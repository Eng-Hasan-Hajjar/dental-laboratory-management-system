<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Doctor,Patient,WorkType};
use Illuminate\Http\Request;
class DirectoryApiController extends Controller {
 public function doctors(Request $r){return response()->json(Doctor::orderBy('name')->paginate(min(50,max(1,(int)$r->query('per_page',15)))));}
 public function patients(Request $r){return response()->json(Patient::with('doctor:id,name')->orderBy('name')->paginate(min(50,max(1,(int)$r->query('per_page',15)))));}
 public function workTypes(){return response()->json(WorkType::where('is_active',true)->orderBy('name')->get());}
}
