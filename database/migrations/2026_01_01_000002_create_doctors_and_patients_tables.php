<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::create('doctors',function(Blueprint $t){$t->id();$t->string('name');$t->string('phone',40)->nullable();$t->string('email')->nullable();$t->string('specialty')->nullable();$t->string('clinic_name')->nullable();$t->text('address')->nullable();$t->text('notes')->nullable();$t->timestamps();});
  Schema::create('patients',function(Blueprint $t){$t->id();$t->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->string('phone',40)->nullable();$t->date('date_of_birth')->nullable();$t->string('gender',10)->nullable();$t->text('notes')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('patients');Schema::dropIfExists('doctors');}
};
