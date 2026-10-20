<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::create('work_types',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->text('description')->nullable();$t->decimal('default_price',12,2)->default(0);$t->unsignedInteger('estimated_days')->default(7);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('lab_orders',function(Blueprint $t){$t->id();$t->string('order_no',32)->unique();$t->foreignId('doctor_id')->constrained()->restrictOnDelete();$t->foreignId('patient_id')->constrained()->restrictOnDelete();$t->foreignId('work_type_id')->constrained()->restrictOnDelete();$t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();$t->string('shade',50)->nullable();$t->string('material')->nullable();$t->string('tooth_numbers')->nullable();$t->text('instructions')->nullable();$t->date('received_at');$t->date('due_at');$t->decimal('price',12,2);$t->string('priority',15)->default('normal');$t->string('status',25)->default('received')->index();$t->timestamps();$t->index(['due_at','status']);});
  Schema::create('order_stage_histories',function(Blueprint $t){$t->id();$t->foreignId('lab_order_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('from_status',25)->nullable();$t->string('to_status',25);$t->text('note')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('order_stage_histories');Schema::dropIfExists('lab_orders');Schema::dropIfExists('work_types');}
};
