<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabOrder extends Model {
 public const STATUSES = ['received'=>'مستلم','designing'=>'التصميم','manufacturing'=>'قيد التصنيع','quality_control'=>'فحص الجودة','ready'=>'جاهز','delivered'=>'تم التسليم','cancelled'=>'ملغي'];
 public const TRANSITIONS = [
  'received'=>['designing','cancelled'], 'designing'=>['manufacturing','cancelled'],
  'manufacturing'=>['quality_control','cancelled'], 'quality_control'=>['ready','manufacturing'],
  'ready'=>['delivered','quality_control'], 'delivered'=>[], 'cancelled'=>[],
 ];
 protected $fillable=['order_no','doctor_id','patient_id','work_type_id','assigned_to','shade','material','tooth_numbers','instructions','received_at','due_at','price','priority','status'];
 protected function casts():array{return ['doctor_id'=>'integer','patient_id'=>'integer','work_type_id'=>'integer','assigned_to'=>'integer','received_at'=>'date','due_at'=>'date','price'=>'decimal:2'];}
 public function doctor(){return $this->belongsTo(Doctor::class);}
 public function patient(){return $this->belongsTo(Patient::class);}
 public function workType(){return $this->belongsTo(WorkType::class);}
 public function technician(){return $this->belongsTo(User::class,'assigned_to');}
 public function history(){return $this->hasMany(OrderStageHistory::class)->latest();}
 public function invoice(){return $this->hasOne(Invoice::class);}
 public function getIsLateAttribute():bool{return $this->due_at?->isBefore(today()) && !in_array($this->status,['delivered','cancelled'],true);}
 public function getStatusLabelAttribute():string{return self::STATUSES[$this->status]??$this->status;}
 public function nextStatuses():array {return self::TRANSITIONS[$this->status]??[];}
}
