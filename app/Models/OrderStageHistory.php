<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderStageHistory extends Model {
 protected $fillable=['lab_order_id','user_id','from_status','to_status','note'];
 public function order(){return $this->belongsTo(LabOrder::class,'lab_order_id');}
 public function user(){return $this->belongsTo(User::class);}
}
