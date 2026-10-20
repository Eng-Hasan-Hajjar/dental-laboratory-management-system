<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::create('invoices',function(Blueprint $t){$t->id();$t->string('number',32)->unique();$t->foreignId('lab_order_id')->unique()->constrained()->restrictOnDelete();$t->decimal('subtotal',12,2);$t->decimal('discount',12,2)->default(0);$t->decimal('tax',12,2)->default(0);$t->decimal('total',12,2);$t->date('issued_at');$t->text('notes')->nullable();$t->timestamps();});
  Schema::create('payments',function(Blueprint $t){$t->id();$t->foreignId('invoice_id')->constrained()->restrictOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->decimal('amount',12,2);$t->string('method',20);$t->string('reference')->nullable();$t->date('paid_at');$t->text('notes')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('payments');Schema::dropIfExists('invoices');}
};
