<?php
namespace Tests\Feature;
use App\Models\{Doctor,Patient,WorkType,User,LabOrder,Invoice};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class LabWorkflowTest extends TestCase {
 use RefreshDatabase;
 private function actor(string $role):User{return User::create(['name'=>'Test','email'=>uniqid().'@example.test','password'=>'valid-password-123','role'=>$role,'is_active'=>true]);}
 private function order(User $user):LabOrder{
  $d=Doctor::create(['name'=>'Dr Test']);$p=Patient::create(['name'=>'Patient Test','doctor_id'=>$d->id]);$w=WorkType::create(['name'=>'Work Test','default_price'=>100,'estimated_days'=>4,'is_active'=>true]);
  $this->actingAs($user)->post(route('orders.store'),['doctor_id'=>$d->id,'patient_id'=>$p->id,'work_type_id'=>$w->id,'received_at'=>'2026-10-01','due_at'=>'2026-10-10','price'=>100,'priority'=>'normal'])->assertRedirect();
  return LabOrder::firstOrFail();
 }
 public function test_creating_order_generates_invoice_and_history():void{$user=$this->actor('admin');$o=$this->order($user);$this->assertDatabaseCount('lab_orders',1);$this->assertDatabaseHas('invoices',['lab_order_id'=>$o->id,'total'=>100]);$this->assertDatabaseHas('order_stage_histories',['lab_order_id'=>$o->id,'to_status'=>'received']);}
 public function test_technician_cannot_create_order():void{$u=$this->actor('technician');$this->actingAs($u)->get(route('orders.create'))->assertForbidden();}
 public function test_payment_over_total_is_rejected():void{$u=$this->actor('admin');$o=$this->order($u);$invoice=$o->invoice;$this->actingAs($u)->post(route('payments.store',$invoice),['amount'=>120,'method'=>'cash','paid_at'=>'2026-10-01'])->assertSessionHasErrors('amount');$this->assertDatabaseCount('payments',0);}
 public function test_invalid_status_transition_is_rejected():void{$u=$this->actor('admin');$o=$this->order($u);$this->actingAs($u)->patch(route('orders.status',$o),['status'=>'delivered'])->assertSessionHasErrors('status');$this->assertEquals('received',$o->fresh()->status);}
 public function test_guest_is_redirected_to_login():void{$this->get(route('dashboard'))->assertRedirect(route('login'));}
}
