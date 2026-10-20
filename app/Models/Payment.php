<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
 protected $fillable=['invoice_id','user_id','amount','method','reference','paid_at','notes'];
 protected function casts():array{return ['amount'=>'decimal:2','paid_at'=>'date'];}
 public function invoice(){return $this->belongsTo(Invoice::class);}
 public function user(){return $this->belongsTo(User::class);}
}
