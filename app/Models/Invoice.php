<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Invoice extends Model {
 protected $fillable=['number','lab_order_id','subtotal','discount','tax','total','issued_at','notes'];
 protected function casts():array{return ['subtotal'=>'decimal:2','discount'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2','issued_at'=>'date'];}
 public function order(){return $this->belongsTo(LabOrder::class,'lab_order_id');}
 public function payments(){return $this->hasMany(Payment::class);}
 public function getPaidAmountAttribute():float {return (float)($this->payments_sum_amount ?? $this->payments()->sum('amount'));}
 public function getBalanceAttribute():float {return max(0,round((float)$this->total-$this->paid_amount,2));}
 public function getPaymentStatusAttribute():string {if($this->balance<=0)return 'مدفوعة';return $this->paid_amount>0?'مدفوعة جزئياً':'غير مدفوعة';}
}
