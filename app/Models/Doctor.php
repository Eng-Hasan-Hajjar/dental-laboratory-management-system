<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Doctor extends Model {
 protected $fillable = ['name','phone','email','specialty','clinic_name','address','notes'];
 public function patients() { return $this->hasMany(Patient::class); }
 public function orders() { return $this->hasMany(LabOrder::class); }
}
