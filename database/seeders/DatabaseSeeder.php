<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\WorkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
class DatabaseSeeder extends Seeder {
 public function run():void{
  $email=env('ADMIN_EMAIL');$password=env('ADMIN_PASSWORD');
  if(! $email || ! $password || strlen($password)<12){throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD (12+ characters) in .env before db:seed.');}
  User::updateOrCreate(['email'=>$email],['name'=>env('ADMIN_NAME','Admin'),'password'=>Hash::make($password),'role'=>'admin','is_active'=>true]);
  foreach ([['تاج زيركون', 'Zircon crown',100,5],['جسر أسنان','Dental bridge',250,7],['طقم أسنان كامل','Complete denture',400,10],['قشرة تجميلية','Veneer',120,6],['تقويم متحرك','Removable orthodontic appliance',180,8],['ترميم مؤقت','Temporary restoration',55,3]] as [$name,$desc,$price,$days]){
   WorkType::firstOrCreate(['name'=>$name],['description'=>$desc,'default_price'=>$price,'estimated_days'=>$days,'is_active'=>true]);
  }
  if (filter_var(env('DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) { $this->call(DemoSeeder::class); }
 }
}
