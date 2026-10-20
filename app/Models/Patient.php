<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Patient extends Model {
 protected $fillable = ['name','phone','date_of_birth','gender','doctor_id','notes'];
 protected function casts(): array {return ['date_of_birth'=>'date'];}
 public function doctor() { return $this->belongsTo(Doctor::class); }
 public function orders() { return $this->hasMany(LabOrder::class); }
}
