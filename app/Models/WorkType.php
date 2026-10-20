<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WorkType extends Model {
 protected $fillable=['name','description','default_price','estimated_days','is_active'];
 protected function casts():array{return ['is_active'=>'boolean','default_price'=>'decimal:2'];}
 public function orders(){return $this->hasMany(LabOrder::class);}
}
